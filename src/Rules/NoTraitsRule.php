<?php

declare(strict_types=1);

namespace Innis\CodingStandards\Rules;

use Override;
use PhpParser\Node;
use PhpParser\Node\Stmt\Trait_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Traits are banned ecosystem-wide; behaviour is shared through injected collaborators.
 *
 * @implements Rule<Trait_>
 */
final class NoTraitsRule implements Rule
{
    #[Override]
    public function getNodeType(): string
    {
        return Trait_::class;
    }

    #[Override]
    public function processNode(Node $node, Scope $scope): array
    {
        $name = $node->name?->toString() ?? 'trait';

        return [
            RuleErrorBuilder::message("Traits are banned ecosystem-wide; share behaviour through an injected collaborator ({$name}).")
                ->identifier('innis.noTraits')
                ->build(),
        ];
    }
}
