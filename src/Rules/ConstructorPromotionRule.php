<?php

declare(strict_types=1);

namespace Innis\CodingStandards\Rules;

use Innis\CodingStandards\Support\ClassNames;
use Innis\CodingStandards\Support\DeliberateFence;
use Override;
use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassNode;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * A value object, DTO or entity promotes its constructor properties instead of declaring a
 * field and assigning a same-named parameter to it in the body. Only a plain pass-through
 * assignment (`$this->x = $x`) is flagged — a transforming assignment keeps its body. An
 * ADR-fenced departure is exempt.
 *
 * @implements Rule<InClassNode>
 */
final class ConstructorPromotionRule implements Rule
{
    private const array DATA_NAMESPACES = ['\\ValueObject', '\\DTO', '\\Entity'];

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
        if (ClassNames::isTestNamespace($namespace) || !$this->isDataNamespace($namespace)) {
            return [];
        }

        $constructor = $this->constructor($original);
        if (null === $constructor || null === $constructor->stmts) {
            return [];
        }

        $parameterNames = $this->parameterNames($constructor);
        $errors = [];
        foreach ($constructor->stmts as $statement) {
            $property = $this->passThroughProperty($statement, $parameterNames);
            if (null !== $property) {
                $errors[] = $this->error($property, $statement->getStartLine());
            }
        }

        return $errors;
    }

    private function isDataNamespace(string $namespace): bool
    {
        foreach (self::DATA_NAMESPACES as $segment) {
            if (str_contains($namespace, $segment) || str_starts_with($namespace, ltrim($segment, '\\'))) {
                return true;
            }
        }

        return false;
    }

    private function constructor(Class_ $class): ?ClassMethod
    {
        foreach ($class->getMethods() as $method) {
            if ('__construct' === $method->name->toLowerString()) {
                return $method;
            }
        }

        return null;
    }

    /**
     * @return list<string>
     */
    private function parameterNames(ClassMethod $constructor): array
    {
        $names = [];
        foreach ($constructor->params as $param) {
            if (0 === $param->flags && $param->var instanceof Variable && is_string($param->var->name)) {
                $names[] = $param->var->name;
            }
        }

        return $names;
    }

    /**
     * @param list<string> $parameterNames
     */
    private function passThroughProperty(Node $statement, array $parameterNames): ?string
    {
        if (!$statement instanceof Expression || !$statement->expr instanceof Assign) {
            return null;
        }

        $assign = $statement->expr;
        if (!$assign->var instanceof PropertyFetch
            || !$assign->var->var instanceof Variable
            || 'this' !== $assign->var->var->name
            || !$assign->var->name instanceof Node\Identifier
        ) {
            return null;
        }
        if (!$assign->expr instanceof Variable
            || !is_string($assign->expr->name)
            || !in_array($assign->expr->name, $parameterNames, true)
        ) {
            return null;
        }

        return $assign->var->name->toString();
    }

    private function error(string $property, int $line): IdentifierRuleError
    {
        return RuleErrorBuilder::message("Promote constructor property \${$property} instead of declaring the field and assigning the parameter in the body.")
            ->identifier('innis.promoteConstructorProperties')
            ->line($line)
            ->build();
    }
}
