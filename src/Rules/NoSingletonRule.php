<?php

declare(strict_types=1);

namespace Innis\CodingStandards\Rules;

use Innis\CodingStandards\Support\ClassNames;
use Innis\CodingStandards\Support\DeliberateFence;
use Override;
use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\UnionType;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassNode;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * A class must not be its own global access point. The two unambiguous singleton tells are a
 * static property that holds an instance of the class, and a static `getInstance()` accessor.
 * A collaborator is reached through an injected interface, never a static access point. An
 * ADR-fenced departure is exempt. See docs/adr/0007.
 *
 * @implements Rule<InClassNode>
 */
final class NoSingletonRule implements Rule
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
        if (!$original instanceof Class_ || $this->fence->isFenced($original)) {
            return [];
        }

        $fqcn = $node->getClassReflection()->getName();
        if (ClassNames::isTestNamespace(ClassNames::namespace($fqcn))) {
            return [];
        }

        $shortClassName = ClassNames::short($fqcn);

        return [
            ...$this->propertyErrors($original, $shortClassName),
            ...$this->accessorErrors($original, $shortClassName),
        ];
    }

    /**
     * @return list<IdentifierRuleError>
     */
    private function propertyErrors(Class_ $class, string $shortClassName): array
    {
        $errors = [];
        foreach ($class->getProperties() as $property) {
            if ($property->isStatic() && $this->isOwnType($property->type, $shortClassName)) {
                $errors[] = $this->error(
                    "Class {$shortClassName} holds a static instance of itself in \${$property->props[0]->name->toString()}; this is a singleton — depend on an injected interface, not a global access point.",
                    $property->getStartLine(),
                );
            }
        }

        return $errors;
    }

    /**
     * @return list<IdentifierRuleError>
     */
    private function accessorErrors(Class_ $class, string $shortClassName): array
    {
        $method = $class->getMethod('getInstance');
        if (null === $method || !$method->isStatic()) {
            return [];
        }

        return [
            $this->error(
                "Class {$shortClassName} exposes a static getInstance() accessor; a singleton/service locator hides its dependency — inject it through an interface instead.",
                $method->getStartLine(),
            ),
        ];
    }

    private function isOwnType(?Node $type, string $shortClassName): bool
    {
        if ($type instanceof NullableType) {
            return $this->isOwnType($type->type, $shortClassName);
        }
        if ($type instanceof UnionType) {
            foreach ($type->types as $member) {
                if ($this->isOwnType($member, $shortClassName)) {
                    return true;
                }
            }

            return false;
        }
        if ($type instanceof Name) {
            $short = ClassNames::short($type->toString());

            return in_array(strtolower($short), ['self', 'static'], true) || $short === $shortClassName;
        }

        return false;
    }

    private function error(string $message, int $line): IdentifierRuleError
    {
        return RuleErrorBuilder::message($message)
            ->identifier('innis.noSingleton')
            ->line($line)
            ->build();
    }
}
