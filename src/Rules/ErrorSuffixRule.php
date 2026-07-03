<?php

declare(strict_types=1);

namespace Innis\CodingStandards\Rules;

use Innis\CodingStandards\Support\ClassNames;
use Override;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use Throwable;

/**
 * A non-throwable class must not use the `*Error` suffix: in PHP `\Error` is a `Throwable`,
 * so an `*Error` name on a returned value misleads. A returned outcome value uses `*Failure`.
 *
 * @implements Rule<InClassNode>
 */
final class ErrorSuffixRule implements Rule
{
    #[Override]
    public function getNodeType(): string
    {
        return InClassNode::class;
    }

    #[Override]
    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node->getOriginalNode() instanceof Class_) {
            return [];
        }

        $reflection = $node->getClassReflection();
        if (ClassNames::isTestNamespace(ClassNames::namespace($reflection->getName()))) {
            return [];
        }

        $name = ClassNames::short($reflection->getName());
        if (!str_ends_with($name, 'Error') || $reflection->isSubclassOf(Throwable::class)) {
            return [];
        }

        return [
            RuleErrorBuilder::message("{$name} is not throwable; a returned outcome value uses the *Failure suffix, not *Error (\\Error is a Throwable).")
                ->identifier('innis.errorSuffix')
                ->line($node->getStartLine())
                ->build(),
        ];
    }
}
