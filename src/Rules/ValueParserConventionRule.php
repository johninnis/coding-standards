<?php

declare(strict_types=1);

namespace Innis\CodingStandards\Rules;

use Innis\CodingStandards\Support\ClassNames;
use Innis\CodingStandards\Support\DeliberateFence;
use Override;
use PhpParser\Node;
use PhpParser\Node\ComplexType;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\UnionType;
use PhpParser\NodeFinder;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassNode;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Named constructors follow PHP's own from/tryFrom split. A `tryFrom*` parses untrusted input
 * and reports failure as a value: it returns `?self` (or a `*Failure` union) and never throws,
 * so "you didn't handle the failure" is an analyser error the caller cannot miss. A `from*` is
 * total, trusted construction: it may throw to assert an invariant, and it does not return
 * nullable — a nullable `from*` is a `tryFrom*` under the wrong name. An ADR-fenced departure (a
 * `tryFrom*` that translates a thrown library fault at its own boundary) is exempt.
 *
 * @implements Rule<InClassNode>
 */
final class ValueParserConventionRule implements Rule
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

        $reflection = $node->getClassReflection();
        if (ClassNames::isTestNamespace(ClassNames::namespace($reflection->getName()))) {
            return [];
        }

        $shortClassName = ClassNames::short($reflection->getName());
        $errors = [];
        foreach ($original->getMethods() as $method) {
            foreach ($this->methodErrors($method, $shortClassName) as $error) {
                $errors[] = $error;
            }
        }

        return $errors;
    }

    /**
     * @return list<IdentifierRuleError>
     */
    private function methodErrors(ClassMethod $method, string $shortClassName): array
    {
        if (!$method->isStatic() || !$method->isPublic() || $this->fence->isFenced($method)) {
            return [];
        }
        if (!$this->returnsOwnType($method->returnType, $shortClassName)) {
            return [];
        }

        $name = $method->name->toString();
        if (1 === preg_match('/^tryFrom([A-Z]|$)/', $name)) {
            return $this->tryFromErrors($method, $name);
        }
        if (1 === preg_match('/^from([A-Z]|$)/', $name)) {
            return $this->fromErrors($method, $name);
        }

        return [];
    }

    /**
     * A `tryFrom*` parses untrusted input: it must return nullable and must not throw.
     *
     * @return list<IdentifierRuleError>
     */
    private function tryFromErrors(ClassMethod $method, string $name): array
    {
        $errors = [];
        if (!$this->isNullable($method->returnType)) {
            $errors[] = $this->error(
                "Untrusted-input parser {$name}() returns the constructed type non-nullably; a tryFrom parser returns ?self (or a *Failure) so the caller must handle bad input.",
                'innis.tryFromReturnsNullable',
                $method->getStartLine(),
            );
        }
        if ($this->throws($method)) {
            $errors[] = $this->error(
                "Untrusted-input parser {$name}() throws; a tryFrom parser reports bad input as null, it does not throw a fault.",
                'innis.tryFromDoesNotThrow',
                $method->getStartLine(),
            );
        }

        return $errors;
    }

    /**
     * A `from*` is total, trusted construction: it does not return nullable. A nullable one
     * reports failure as a value and belongs under the `tryFrom*` name.
     *
     * @return list<IdentifierRuleError>
     */
    private function fromErrors(ClassMethod $method, string $name): array
    {
        if (!$this->isNullable($method->returnType)) {
            return [];
        }
        $suggested = 'try'.ucfirst($name);

        return [
            $this->error(
                "Total constructor {$name}() returns nullable; a parser that reports failure as a value belongs under the tryFrom name — rename it {$suggested}() (a from constructor is total and throws to assert an invariant instead).",
                'innis.fromIsTotal',
                $method->getStartLine(),
            ),
        ];
    }

    private function returnsOwnType(?Node $returnType, string $shortClassName): bool
    {
        return null !== $this->ownTypeName($returnType, $shortClassName);
    }

    private function ownTypeName(?Node $returnType, string $shortClassName): ?string
    {
        if ($returnType instanceof NullableType) {
            return $this->ownTypeName($returnType->type, $shortClassName);
        }
        if ($returnType instanceof UnionType) {
            foreach ($returnType->types as $type) {
                $match = $this->ownTypeName($type, $shortClassName);
                if (null !== $match) {
                    return $match;
                }
            }

            return null;
        }
        if ($returnType instanceof Name) {
            $short = ClassNames::short($returnType->toString());
            if (in_array(strtolower($short), ['self', 'static', 'parent'], true) || $short === $shortClassName) {
                return $short;
            }
        }

        return null;
    }

    private function isNullable(?Node $returnType): bool
    {
        if ($returnType instanceof NullableType) {
            return true;
        }
        if ($returnType instanceof Identifier) {
            return 'null' === $returnType->toLowerString();
        }
        if ($returnType instanceof UnionType) {
            foreach ($returnType->types as $type) {
                if ($this->isNullable($type) || ($type instanceof Identifier && 'null' === $type->toLowerString())) {
                    return true;
                }
                if ($type instanceof Name && str_ends_with(ClassNames::short($type->toString()), 'Failure')) {
                    return true;
                }
            }
        }
        if ($returnType instanceof ComplexType) {
            return false;
        }

        return false;
    }

    private function throws(ClassMethod $method): bool
    {
        if (null === $method->stmts) {
            return false;
        }

        return [] !== (new NodeFinder())->find($method->stmts, static fn (Node $found): bool => $found instanceof Node\Expr\Throw_);
    }

    private function error(string $message, string $identifier, int $line): IdentifierRuleError
    {
        return RuleErrorBuilder::message($message)
            ->identifier($identifier)
            ->line($line)
            ->build();
    }
}
