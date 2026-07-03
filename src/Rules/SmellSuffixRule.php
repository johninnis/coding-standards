<?php

declare(strict_types=1);

namespace Innis\CodingStandards\Rules;

use Innis\CodingStandards\Support\ClassNames;
use Innis\CodingStandards\Support\DeliberateFence;
use Override;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Catch-all class-name suffixes are a design smell: name a class for what it does.
 * These are warnings — ignore the identifier if a name is a genuine exception.
 *
 * @implements Rule<InClassNode>
 */
final class SmellSuffixRule implements Rule
{
    private const array SMELLS = [
        'Adapter' => ['innis.adapterSuffix', 'reserve the *Adapter suffix for a true GoF adapter; name an implementation for what it does'],
        'Manager' => ['innis.managerSuffix', 'the *Manager suffix is a catch-all smell unless it is a genuine lifecycle/registry'],
        'Service' => ['innis.serviceSuffix', 'a bare *Service suffix is a catch-all smell; name the class for what it does'],
    ];

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
        if (!$original instanceof Class_) {
            return [];
        }

        $fqcn = $node->getClassReflection()->getName();
        if (ClassNames::isTestNamespace(ClassNames::namespace($fqcn)) || $this->fence->isFenced($original)) {
            return [];
        }

        $name = ClassNames::short($fqcn);
        foreach (self::SMELLS as $suffix => [$identifier, $advice]) {
            if (str_ends_with($name, $suffix)) {
                return [
                    RuleErrorBuilder::message("{$name}: {$advice}.")
                        ->identifier($identifier)
                        ->line($node->getStartLine())
                        ->build(),
                ];
            }
        }

        return [];
    }
}
