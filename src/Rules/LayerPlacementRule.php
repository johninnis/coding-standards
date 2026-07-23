<?php

declare(strict_types=1);

namespace Innis\CodingStandards\Rules;

use Innis\CodingStandards\Support\ClassNames;
use Innis\CodingStandards\Support\DeliberateFence;
use Innis\CodingStandards\Support\Layer;
use Override;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\Interface_;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * A file with no layer segment is invisible to {@see CleanArchitectureLayeringRule}, by design:
 * unlayered code is not layered code. That exemption exists for the composition root — a container
 * or bootstrap whose methods only construct and wire — and it is the one way to hold behaviour
 * while importing outward unchecked.
 *
 * Implementing or extending a layered contract is the tell that a class is layered code: it is
 * bound to Domain, Application, Infrastructure or Presentation and must be filed there, taking the
 * inward rule with it. A class that seems to need the root because it would otherwise import
 * outward wants the dependency inverted behind a port, not the file moved. Contracts carrying no
 * layer segment (a framework's, a library's) say nothing about placement and are ignored, and an
 * ADR-fenced departure is exempt.
 *
 * An anonymous class has no name, so it has no namespace of its own and nothing to read a layer
 * from. It is filed wherever it is declared, and it is judged there: an inline double in a test
 * mirrors the test's namespace, one built inside a layered file belongs to that layer, and one in
 * an unnamespaced script is part of the composition root — a file PSR-4 cannot autoload can only be
 * an entry point. What remains reportable is an anonymous class declared in a namespaced file that
 * carries no layer, and its fix is to file that declaring file, since the class cannot move without
 * first being named.
 *
 * @implements Rule<InClassNode>
 */
final class LayerPlacementRule implements Rule
{
    public function __construct(private readonly DeliberateFence $fence)
    {
    }

    #[Override]
    public function getNodeType(): string
    {
        return InClassNode::class;
    }

    #[Override]
    public function processNode(Node $node, Scope $scope): array
    {
        $original = $node->getOriginalNode();
        if ($this->fence->isFenced($original)) {
            return [];
        }

        $reflection = $node->getClassReflection();
        $anonymous = $reflection->isAnonymous();

        $namespace = $anonymous ? $scope->getNamespace() : ClassNames::namespace($reflection->getName());
        if (null === $namespace || ClassNames::isTestNamespace($namespace) || null !== Layer::of($namespace)) {
            return [];
        }

        $subject = $anonymous ? 'The anonymous class' : ClassNames::short($reflection->getName());
        $remedy = $anonymous ? 'file its declaring file' : 'file it';

        $errors = [];
        foreach ($this->contracts($original) as $verb => $contracts) {
            foreach ($contracts as $contract) {
                $layer = Layer::of($contract);
                if (null === $layer) {
                    continue;
                }

                $errors[] = RuleErrorBuilder::message("{$subject} {$verb} the {$layer} contract {$contract} but sits outside the layers; only composition belongs there — {$remedy} under a layer so its dependencies are checked.")
                    ->identifier('innis.layerPlacement')
                    ->line($node->getStartLine())
                    ->build();
            }
        }

        return $errors;
    }

    /**
     * The contracts named in the declaration itself, keyed by the verb that named them. Inherited
     * and transitively-extended contracts are deliberately not walked: the rule reports what the
     * file says, matching the import-only reading of the layering rule it backs up.
     *
     * @return array<string, list<string>>
     */
    private function contracts(Node $declaration): array
    {
        if ($declaration instanceof Class_) {
            return [
                'extends' => null === $declaration->extends ? [] : [$declaration->extends->toString()],
                'implements' => $this->names($declaration->implements),
            ];
        }
        if ($declaration instanceof Interface_) {
            return ['extends' => $this->names($declaration->extends)];
        }
        if ($declaration instanceof Enum_) {
            return ['implements' => $this->names($declaration->implements)];
        }

        return [];
    }

    /**
     * @param array<Node\Name> $names
     *
     * @return list<string>
     */
    private function names(array $names): array
    {
        return array_values(array_map(static fn (Node\Name $name): string => $name->toString(), $names));
    }
}
