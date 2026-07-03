<?php

declare(strict_types=1);

namespace Innis\CodingStandards\Rules;

use Override;
use PhpParser\Node;
use PhpParser\Node\Stmt\Interface_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Every interface name ends in 'Interface'.
 *
 * @implements Rule<Interface_>
 */
final class InterfaceNamingRule implements Rule
{
    #[Override]
    public function getNodeType(): string
    {
        return Interface_::class;
    }

    #[Override]
    public function processNode(Node $node, Scope $scope): array
    {
        $name = $node->name?->toString();
        if (null === $name || str_ends_with($name, 'Interface')) {
            return [];
        }

        return [
            RuleErrorBuilder::message("Interface {$name} must end in 'Interface'.")
                ->identifier('innis.interfaceNaming')
                ->build(),
        ];
    }
}
