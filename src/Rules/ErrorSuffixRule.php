<?php

declare(strict_types=1);

namespace Innis\CodingStandards\Rules;

use Innis\CodingStandards\Support\ClassNames;
use Override;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Enum_;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use Throwable;

/**
 * A non-throwable class or enum must not use the `*Error` suffix: in PHP `\Error` is a `Throwable`,
 * so an `*Error` name on a returned value misleads. A returned outcome value uses `*Failure`.
 *
 * Enums are judged alongside classes because an enum is the strongest form of the mistake, not an
 * exemption from it: an enum can never extend `Throwable`, so an `*Error` enum is guaranteed to be
 * the returned outcome value the suffix denies it is. Returned outcomes are routinely modelled as
 * enums here, so this is the case most likely to be reached for.
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
        $original = $node->getOriginalNode();
        if (!$original instanceof Class_ && !$original instanceof Enum_) {
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
