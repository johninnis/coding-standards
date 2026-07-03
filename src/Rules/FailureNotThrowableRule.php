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
 * A `*Failure` is a returned outcome value; it must not be throwable. A thrown fault
 * uses the `*Exception` suffix instead.
 *
 * @implements Rule<InClassNode>
 */
final class FailureNotThrowableRule implements Rule
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
        if (!str_ends_with($name, 'Failure') || !$reflection->isSubclassOf(Throwable::class)) {
            return [];
        }

        return [
            RuleErrorBuilder::message("{$name} is a returned outcome value and must not be throwable; a thrown fault uses the *Exception suffix.")
                ->identifier('innis.failureNotThrowable')
                ->build(),
        ];
    }
}
