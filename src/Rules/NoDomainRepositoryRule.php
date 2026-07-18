<?php

declare(strict_types=1);

namespace Innis\CodingStandards\Rules;

use Innis\CodingStandards\Support\ClassNames;
use Innis\CodingStandards\Support\DeliberateFence;
use Override;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * There is no `Domain/Repository`. A persistence store is a driven port the host supplies and
 * can swap, so its interface belongs in `Application/Port/` beside the clock, the HTTP client, and
 * the other ports — never in the Domain. Any type filed under a `Domain\Repository` namespace is
 * mis-placed, whether interface, class, or enum. An ADR-fenced departure is exempt.
 *
 * @implements Rule<InClassNode>
 */
final class NoDomainRepositoryRule implements Rule
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
        if ($this->fence->isFenced($node->getOriginalNode())) {
            return [];
        }

        $namespace = ClassNames::namespace($node->getClassReflection()->getName());
        if (ClassNames::isTestNamespace($namespace) || !ClassNames::hasSegment($namespace, 'Domain\\Repository')) {
            return [];
        }

        $name = ClassNames::short($node->getClassReflection()->getName());

        return [
            RuleErrorBuilder::message("{$name} is under Domain/Repository; a persistence store is a driven port — file its interface in Application/Port, not Domain.")
                ->identifier('innis.noDomainRepository')
                ->line($node->getStartLine())
                ->build(),
        ];
    }
}
