<?php

declare(strict_types=1);

namespace Innis\CodingStandards\Rules\PortPlacement;

use Innis\CodingStandards\Support\ClassNames;
use Override;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Collectors\Collector;
use PHPStan\Node\InClassNode;

/**
 * Collects each `Application/Service` class together with the short names of the
 * interfaces it directly implements.
 *
 * @implements Collector<InClassNode, array{name: string, interfaces: list<string>, line: int}>
 */
final class ServiceImplementorCollector implements Collector
{
    #[Override]
    public function getNodeType(): string
    {
        return InClassNode::class;
    }

    /**
     * @return array{name: string, interfaces: list<string>, line: int}|null
     */
    #[Override]
    public function processNode(Node $node, Scope $scope): ?array
    {
        $original = $node->getOriginalNode();
        if (!$original instanceof Class_) {
            return null;
        }

        $namespace = ClassNames::namespace($node->getClassReflection()->getName());
        if (ClassNames::isTestNamespace($namespace) || !ClassNames::hasSegment($namespace, 'Application\\Service')) {
            return null;
        }

        $interfaces = [];
        foreach ($original->implements as $implemented) {
            $interfaces[] = ClassNames::short($implemented->toString());
        }
        if ([] === $interfaces) {
            return null;
        }

        return [
            'name' => ClassNames::short($node->getClassReflection()->getName()),
            'interfaces' => $interfaces,
            'line' => $original->getStartLine(),
        ];
    }
}
