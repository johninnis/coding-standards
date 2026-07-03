<?php

declare(strict_types=1);

namespace Innis\CodingStandards\Rules;

use Innis\CodingStandards\Support\ClassNames;
use Innis\CodingStandards\Support\DeliberateFence;
use Override;
use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\UnionType;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassNode;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * An equality method on a value object or entity types its parameter as `self`, so a sibling
 * value cannot be compared. Widening the parameter to a shared base, an interface, `object`,
 * `mixed`, or leaving it untyped lets a different type through and the analyser can no longer
 * stop `$publicKey->equals($eventId)`. An ADR-fenced departure is exempt.
 *
 * @implements Rule<InClassNode>
 */
final class EqualsSelfRule implements Rule
{
    private const array EQUALITY_METHODS = [
        'equals', 'equalto', 'equalsto', 'isequal', 'isequalto', 'sameas', 'samevalueas', 'hassamevalueas',
    ];

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

        $namespace = ClassNames::namespace($node->getClassReflection()->getName());
        if (ClassNames::isTestNamespace($namespace) || !$this->isValueType($namespace)) {
            return [];
        }

        $shortClassName = ClassNames::short($node->getClassReflection()->getName());
        $errors = [];
        foreach ($original->getMethods() as $method) {
            $error = $this->methodError($method, $shortClassName);
            if (null !== $error) {
                $errors[] = $error;
            }
        }

        return $errors;
    }

    private function isValueType(string $namespace): bool
    {
        return ClassNames::hasSegment($namespace, 'ValueObject') || ClassNames::hasSegment($namespace, 'Entity');
    }

    private function methodError(ClassMethod $method, string $shortClassName): ?IdentifierRuleError
    {
        if (!$method->isPublic() || !in_array($method->name->toLowerString(), self::EQUALITY_METHODS, true) || $this->fence->isFenced($method)) {
            return null;
        }

        $parameter = $method->params[0] ?? null;
        if (null === $parameter || $this->isSelfType($parameter->type, $shortClassName)) {
            return null;
        }

        return RuleErrorBuilder::message("{$shortClassName}::{$method->name->toString()}() accepts {$this->render($parameter->type)}; a value object compares only against its own type — type the parameter as self.")
            ->identifier('innis.equalsSelf')
            ->line($method->getStartLine())
            ->build();
    }

    private function isSelfType(?Node $type, string $shortClassName): bool
    {
        if (!$type instanceof Name) {
            return false;
        }
        $short = ClassNames::short($type->toString());

        return in_array(strtolower($short), ['self', 'static'], true) || $short === $shortClassName;
    }

    private function render(?Node $type): string
    {
        return match (true) {
            null === $type => 'an untyped parameter',
            $type instanceof NullableType => '?'.$this->render($type->type),
            $type instanceof UnionType => implode('|', array_map($this->render(...), $type->types)),
            $type instanceof Name => ClassNames::short($type->toString()),
            $type instanceof Identifier => $type->toString(),
            default => 'a non-self type',
        };
    }
}
