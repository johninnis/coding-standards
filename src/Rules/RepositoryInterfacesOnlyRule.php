<?php

declare(strict_types=1);

namespace Innis\CodingStandards\Rules;

use Innis\CodingStandards\Support\ClassNames;
use Innis\CodingStandards\Support\DeliberateFence;
use Override;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\Interface_;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * `Domain/Repository` holds interfaces only — a repository is a driven port the Domain
 * owns and Infrastructure implements. An ADR-fenced departure is exempt.
 *
 * @implements Rule<InClassNode>
 */
final class RepositoryInterfacesOnlyRule implements Rule
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
        if ($original instanceof Interface_ || $this->fence->isFenced($original)) {
            return [];
        }

        $namespace = ClassNames::namespace($node->getClassReflection()->getName());
        if (ClassNames::isTestNamespace($namespace) || !ClassNames::hasSegment($namespace, 'Domain\\Repository')) {
            return [];
        }

        $kind = match (true) {
            $original instanceof Enum_ => 'enum',
            $original instanceof Class_ => 'class',
            default => 'type',
        };
        $name = ClassNames::short($node->getClassReflection()->getName());

        return [
            RuleErrorBuilder::message("Domain/Repository holds interfaces only; {$name} is a {$kind}.")
                ->identifier('innis.repositoryInterfacesOnly')
                ->line($node->getStartLine())
                ->build(),
        ];
    }
}
