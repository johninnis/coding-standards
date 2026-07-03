<?php

declare(strict_types=1);

namespace Innis\CodingStandards\Rules;

use Countable;
use Innis\CodingStandards\Support\ClassNames;
use Innis\CodingStandards\Support\TypedCollections;
use IteratorAggregate;
use Override;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassNode;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * A concrete typed collection (a `*Collection`, or a leaf extending `TypedCollection`) is a
 * `final` class that implements `IteratorAggregate` and `Countable`, so its element type is
 * guaranteed by construction and it is iterable and countable at every boundary. The
 * abstract `TypedCollection` base is exempt.
 *
 * @implements Rule<InClassNode>
 */
final class CollectionContractRule implements Rule
{
    #[Override]
    public function getNodeType(): string
    {
        return InClassNode::class;
    }

    #[Override]
    public function processNode(Node $node, Scope $scope): array
    {
        $original = $node->getOriginalNode();
        if (!$original instanceof Class_ || $original->isAbstract()) {
            return [];
        }

        $reflection = $node->getClassReflection();
        $name = ClassNames::short($reflection->getName());
        if (ClassNames::isTestNamespace(ClassNames::namespace($reflection->getName())) || !TypedCollections::isConcrete($original, $name)) {
            return [];
        }

        $errors = [];
        if (!$original->isFinal()) {
            $errors[] = $this->error("Typed collection {$name} must be 'final'.", $original->getStartLine());
        }
        foreach ([IteratorAggregate::class, Countable::class] as $contract) {
            if (!$this->implements($reflection, $contract)) {
                $errors[] = $this->error("Typed collection {$name} must implement ".ClassNames::short($contract).'.', $original->getStartLine());
            }
        }

        return $errors;
    }

    private function implements(ClassReflection $reflection, string $contract): bool
    {
        return $reflection->implementsInterface($contract) || $reflection->isSubclassOf($contract);
    }

    private function error(string $message, int $line): IdentifierRuleError
    {
        return RuleErrorBuilder::message($message)
            ->identifier('innis.collectionContract')
            ->line($line)
            ->build();
    }
}
