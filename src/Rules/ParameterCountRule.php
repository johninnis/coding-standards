<?php

declare(strict_types=1);

namespace Innis\CodingStandards\Rules;

use Innis\CodingStandards\Support\ClassNames;
use Innis\CodingStandards\Support\DeliberateFence;
use Override;
use PhpParser\Node;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * More than three parameters on a function, method or constructor is a design signal: the
 * unit usually has too many responsibilities. The fix is decomposition — split the unit —
 * not bundling unrelated arguments into a parameter object. This is a smell; ignore the
 * identifier where a cohesive value object genuinely carries the arguments.
 *
 * @implements Rule<FunctionLike>
 */
final class ParameterCountRule implements Rule
{
    private const int MAX_PARAMETERS = 3;

    public function __construct(private readonly DeliberateFence $fence)
    {
    }

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
        if (ClassNames::isTestNamespace($scope->getNamespace() ?? '') || $this->fence->isFenced($node)) {
            return [];
        }
        if ($node instanceof ClassMethod && '__construct' === $node->name->toLowerString() && $this->isDataRecord($node, $scope)) {
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

    /**
     * A `readonly` class whose constructor promotes every parameter is a pure data record; its
     * fields are the cohesive value, and a wide wire-format value object (an Event, a metadata
     * struct) has nowhere to decompose to. The count signal is for behavioural units, not these.
     */
    private function isDataRecord(ClassMethod $constructor, Scope $scope): bool
    {
        if (!$scope->isInClass() || !$scope->getClassReflection()->isReadOnly()) {
            return false;
        }
        foreach ($constructor->getParams() as $param) {
            if (0 === $param->flags) {
                return false;
            }
        }

        return true;
    }
}
