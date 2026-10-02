<?php

declare(strict_types=1);

namespace Innis\CodingStandards\Rules;

use Innis\CodingStandards\Support\ClassNames;
use Override;
use PhpParser\Node;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * More than three parameters on a function, method or constructor is a design signal: the
 * unit usually has too many responsibilities. The fix is decomposition — split the unit —
 * not bundling unrelated arguments into a parameter object.
 *
 * The one exemption is constructing a value: a value's constructor and its static named
 * constructors take the value's own fields. A value is a `final readonly` class under a
 * `ValueObject`, `Entity`, `DTO` or `ViewModel` namespace; a named constructor is a static method
 * returning `self` or `static`, nullable or not. Nothing else is exempt — not a service whose
 * constructor only promotes its collaborators, and not a `// Deliberate:` fence.
 *
 * @implements Rule<FunctionLike>
 */
final class ParameterCountRule implements Rule
{
    private const int MAX_PARAMETERS = 3;

    private const array VALUE_NAMESPACE_SEGMENTS = ['ValueObject', 'Entity', 'DTO', 'ViewModel'];

    #[Override]
    public function getNodeType(): string
    {
        return FunctionLike::class;
    }

    #[Override]
    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node instanceof ClassMethod && !$node instanceof Function_) {
            return [];
        }
        if (ClassNames::isTestNamespace($scope->getNamespace() ?? '')) {
            return [];
        }
        if ($node instanceof ClassMethod && $this->isValueConstruction($node, $scope)) {
            return [];
        }

        $count = count($node->getParams());
        if ($count <= self::MAX_PARAMETERS) {
            return [];
        }

        $name = $node->name->toString();

        return [
            RuleErrorBuilder::message("{$name}() takes {$count} parameters; more than ".self::MAX_PARAMETERS.' is a design signal — decompose the unit rather than bundling arguments into a parameter object.')
                ->identifier('innis.tooManyParameters')
                ->line($node->getStartLine())
                ->build(),
        ];
    }

    private function isValueConstruction(ClassMethod $method, Scope $scope): bool
    {
        if (!$this->isValueClass($scope)) {
            return false;
        }

        return '__construct' === $method->name->toLowerString()
            || ($method->isStatic() && $this->returnsOwnType($method));
    }

    private function isValueClass(Scope $scope): bool
    {
        if (!$scope->isInClass()) {
            return false;
        }
        $class = $scope->getClassReflection();
        $namespace = ClassNames::namespace($class->getName());

        return $class->isFinal()
            && $class->isReadOnly()
            && $this->isInValueNamespace($namespace);
    }

    private function isInValueNamespace(string $namespace): bool
    {
        foreach (self::VALUE_NAMESPACE_SEGMENTS as $segment) {
            if (ClassNames::hasSegment($namespace, $segment)) {
                return true;
            }
        }

        return false;
    }

    private function returnsOwnType(ClassMethod $method): bool
    {
        $type = $method->getReturnType();
        if ($type instanceof NullableType) {
            $type = $type->type;
        }
        if (!$type instanceof Identifier && !$type instanceof Name) {
            return false;
        }

        return in_array($type->toLowerString(), ['self', 'static'], true);
    }
}
