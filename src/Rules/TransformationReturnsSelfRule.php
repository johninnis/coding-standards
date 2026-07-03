<?php

declare(strict_types=1);

namespace Innis\CodingStandards\Rules;

use Innis\CodingStandards\Support\ClassNames;
use Innis\CodingStandards\Support\DeliberateFence;
use Innis\CodingStandards\Support\TypedCollections;
use Override;
use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassNode;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * A transformation on an immutable value, entity, or typed collection returns a new instance,
 * never `void` (or `bool`/`never`). A `void` return is the tell of in-place mutation, or of a
 * caller that will call `$vo->withX()` and lose the result. An ADR-fenced departure is exempt.
 *
 * @implements Rule<InClassNode>
 */
final class TransformationReturnsSelfRule implements Rule
{
    private const array TRANSFORMATION_PREFIXES = [
        'with', 'without', 'add', 'remove', 'append', 'prepend', 'push',
        'map', 'filter', 'merge', 'plus', 'minus', 'concat', 'deduplicate', 'sorted', 'reversed',
    ];

    private const array IN_PLACE_RETURNS = ['void', 'never', 'bool'];

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
        if (!$original instanceof Class_ || $original->isAbstract() || $this->fence->isFenced($original)) {
            return [];
        }

        $namespace = ClassNames::namespace($node->getClassReflection()->getName());
        $shortClassName = ClassNames::short($node->getClassReflection()->getName());
        if (ClassNames::isTestNamespace($namespace) || !$this->isImmutableType($namespace, $original, $shortClassName)) {
            return [];
        }

        $errors = [];
        foreach ($original->getMethods() as $method) {
            $error = $this->methodError($method, $shortClassName);
            if (null !== $error) {
                $errors[] = $error;
            }
        }

        return $errors;
    }

    private function isImmutableType(string $namespace, Class_ $class, string $shortClassName): bool
    {
        return ClassNames::hasSegment($namespace, 'ValueObject')
            || ClassNames::hasSegment($namespace, 'Entity')
            || TypedCollections::isConcrete($class, $shortClassName);
    }

    private function methodError(ClassMethod $method, string $shortClassName): ?IdentifierRuleError
    {
        if (!$method->isPublic() || !$this->isTransformation($method->name->toString()) || $this->fence->isFenced($method)) {
            return null;
        }
        if (!$method->returnType instanceof Identifier || !in_array($method->returnType->toLowerString(), self::IN_PLACE_RETURNS, true)) {
            return null;
        }

        return RuleErrorBuilder::message("{$shortClassName}::{$method->name->toString()}() is a transformation but returns {$method->returnType->toString()}; an immutable value returns a new instance (self), it does not mutate in place.")
            ->identifier('innis.transformationReturnsSelf')
            ->line($method->getStartLine())
            ->build();
    }

    private function isTransformation(string $name): bool
    {
        $lowercased = strtolower($name);
        foreach (self::TRANSFORMATION_PREFIXES as $prefix) {
            if ($lowercased === $prefix) {
                return true;
            }
            if (str_starts_with($lowercased, $prefix) && ctype_upper($name[strlen($prefix)] ?? '')) {
                return true;
            }
        }

        return false;
    }
}
