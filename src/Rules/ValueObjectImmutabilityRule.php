<?php

declare(strict_types=1);

namespace Innis\CodingStandards\Rules;

use Innis\CodingStandards\Support\ClassNames;
use Innis\CodingStandards\Support\DeliberateFence;
use Override;
use PhpParser\Modifiers;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassNode;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * A concrete value object is a `final readonly class`. An `abstract` sum-type base and an
 * ADR-fenced class are exempt. Property-level `readonly` (the sanctioned memory-zeroing
 * exception) is downgraded to a warning so a human can confirm the documented reason.
 *
 * @implements Rule<InClassNode>
 */
final class ValueObjectImmutabilityRule implements Rule
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
        if (!$original instanceof Class_) {
            return [];
        }

        $namespace = ClassNames::namespace($node->getClassReflection()->getName());
        if (ClassNames::isTestNamespace($namespace) || !ClassNames::hasSegment($namespace, 'ValueObject')) {
            return [];
        }

        if ($original->isAbstract() || $this->fence->isFenced($original)) {
            return [];
        }

        $name = ClassNames::short($node->getClassReflection()->getName());

        if (!$original->isFinal()) {
            return [$this->error("Concrete value object {$name} must be 'final'.", $original->getStartLine())];
        }
        if ($original->isReadonly()) {
            return [];
        }
        if ($this->hasReadonlyProperty($original)) {
            return [
                RuleErrorBuilder::message("Value object {$name} uses property-level readonly, not a 'readonly class'; confirm a documented reason (e.g. memory zeroing).")
                    ->identifier('innis.valueObjectImmutable')
                    ->line($original->getStartLine())
                    ->tip('Add a `// Deliberate: …` comment or ADR-NNNN reference to record the reason.')
                    ->build(),
            ];
        }

        return [$this->error("Value object {$name} must be a 'final readonly class'.", $original->getStartLine())];
    }

    private function hasReadonlyProperty(Class_ $class): bool
    {
        foreach ($class->getProperties() as $property) {
            if ($property->isReadonly()) {
                return true;
            }
        }

        foreach ($class->getMethods() as $method) {
            if ('__construct' !== $method->name->toLowerString()) {
                continue;
            }
            foreach ($method->params as $param) {
                if (($param->flags & Modifiers::READONLY) !== 0) {
                    return true;
                }
            }
        }

        return false;
    }

    private function error(string $message, int $line): IdentifierRuleError
    {
        return RuleErrorBuilder::message($message)
            ->identifier('innis.valueObjectImmutable')
            ->line($line)
            ->build();
    }
}
