<?php

declare(strict_types=1);

namespace Innis\CodingStandards\Rules\PortPlacement;

use Innis\CodingStandards\Support\ClassNames;
use Override;
use PhpParser\Node;
use PhpParser\Node\Stmt\Interface_;
use PHPStan\Analyser\Scope;
use PHPStan\Collectors\Collector;
use PHPStan\Node\InClassNode;

/**
 * Collects each `Application/Port` interface.
 *
 * @implements Collector<InClassNode, array{name: string, line: int}>
 */
final class PortInterfaceCollector implements Collector
{
    #[Override]
    public function getNodeType(): string
    {
        return InClassNode::class;
    }

    /**
     * @return array{name: string, line: int}|null
     */
    #[Override]
    public function processNode(Node $node, Scope $scope): ?array
    {
        $original = $node->getOriginalNode();
        if (!$original instanceof Interface_) {
            return null;
        }

        $namespace = ClassNames::namespace($node->getClassReflection()->getName());
        if (ClassNames::isTestNamespace($namespace) || !ClassNames::hasSegment($namespace, 'Application\\Port')) {
            return null;
        }

        return [
            'name' => ClassNames::short($node->getClassReflection()->getName()),
            'line' => $original->getStartLine(),
        ];
    }
}
