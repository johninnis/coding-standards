<?php

declare(strict_types=1);

namespace Innis\CodingStandards\Rules;

use Innis\CodingStandards\Support\ClassNames;
use Override;
use PhpParser\Modifiers;
use PhpParser\Node;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassNode;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * A value object keeps a uniform `getX()` read surface: no property hooks (a computed or
 * interface-bound read is a method) and no asymmetric visibility standing in for a getter.
 * An entity's lifecycle state likewise stays behind `getX()`, mutated through named
 * transformations, not a publicly-readable `private(set)` property.
 *
 * @implements Rule<InClassNode>
 */
final class ValueObjectAccessorsRule implements Rule
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
        if (!$original instanceof Class_) {
            return [];
        }

        $namespace = ClassNames::namespace($node->getClassReflection()->getName());
        if (ClassNames::isTestNamespace($namespace)) {
            return [];
        }
        $kind = $this->kind($namespace);
        if (null === $kind) {
            return [];
        }

        $name = ClassNames::short($node->getClassReflection()->getName());

        return [
            ...$this->propertyErrors($original, $kind, $name),
            ...$this->promotedParameterErrors($original, $kind, $name),
        ];
    }

    private function kind(string $namespace): ?string
    {
        if (ClassNames::hasSegment($namespace, 'ValueObject')) {
            return 'Value object';
        }
        if (ClassNames::hasSegment($namespace, 'Entity')) {
            return 'Entity';
        }

        return null;
    }

    /**
     * @return list<IdentifierRuleError>
     */
    private function propertyErrors(Class_ $class, string $kind, string $className): array
    {
        $errors = [];
        foreach ($class->getProperties() as $property) {
            $propertyName = $property->props[0]->name->toString();
            if ('Value object' === $kind && [] !== $property->hooks) {
                $errors[] = $this->error($this->hookMessage($className, $propertyName), $property->getStartLine());
            }
            if ($this->hasAsymmetricVisibility($property->flags)) {
                $errors[] = $this->error($this->asymmetryMessage($kind, $className, $propertyName), $property->getStartLine());
            }
        }

        return $errors;
    }

    /**
     * @return list<IdentifierRuleError>
     */
    private function promotedParameterErrors(Class_ $class, string $kind, string $className): array
    {
        $constructor = $class->getMethod('__construct');
        if (null === $constructor) {
            return [];
        }

        $errors = [];
        foreach ($constructor->params as $param) {
            $propertyName = $this->promotedName($param);
            if (null === $propertyName) {
                continue;
            }
            if ('Value object' === $kind && [] !== $param->hooks) {
                $errors[] = $this->error($this->hookMessage($className, $propertyName), $param->getStartLine());
            }
            if ($this->hasAsymmetricVisibility($param->flags)) {
                $errors[] = $this->error($this->asymmetryMessage($kind, $className, $propertyName), $param->getStartLine());
            }
        }

        return $errors;
    }

    private function promotedName(Param $param): ?string
    {
        if (0 === $param->flags || !$param->var instanceof Node\Expr\Variable || !is_string($param->var->name)) {
            return null;
        }

        return $param->var->name;
    }

    private function hasAsymmetricVisibility(int $flags): bool
    {
        return 0 !== ($flags & Modifiers::VISIBILITY_SET_MASK);
    }

    private function hookMessage(string $className, string $propertyName): string
    {
        return "Value object {$className}::\${$propertyName} uses a property hook; expose a computed or interface-bound read through a getX() method.";
    }

    private function asymmetryMessage(string $kind, string $className, string $propertyName): string
    {
        $advice = 'Value object' === $kind
            ? 'a value object exposes reads through a getX() method, not private(set)'
            : 'lifecycle state stays behind a getX() method mutated through named transformations, not a private(set) property';

        return "{$kind} {$className}::\${$propertyName} uses asymmetric visibility; {$advice}.";
    }

    private function error(string $message, int $line): IdentifierRuleError
    {
        return RuleErrorBuilder::message($message)
            ->identifier('innis.valueObjectAccessors')
            ->line($line)
            ->build();
    }
}
