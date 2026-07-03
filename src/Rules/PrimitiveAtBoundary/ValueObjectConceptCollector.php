<?php

declare(strict_types=1);

namespace Innis\CodingStandards\Rules\PrimitiveAtBoundary;

use Innis\CodingStandards\Support\ClassNames;
use Override;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Collectors\Collector;
use PHPStan\Node\InClassNode;

/**
 * Collects the short name of every `ValueObject/` class — each is a concept a primitive should be
 * parsed into at the boundary. The rule uses these to know a value object exists for a given name;
 * a primitive with no matching value object is never flagged.
 *
 * @implements Collector<InClassNode, string>
 */
final class ValueObjectConceptCollector implements Collector
{
    #[Override]
    public function getNodeType(): string
    {
        return InClassNode::class;
    }

    #[Override]
    public function processNode(Node $node, Scope $scope): ?string
    {
        if (!$node->getOriginalNode() instanceof Class_) {
            return null;
        }

        $fqcn = $node->getClassReflection()->getName();
        $namespace = ClassNames::namespace($fqcn);
        if (ClassNames::isTestNamespace($namespace) || !ClassNames::hasSegment($namespace, 'ValueObject')) {
            return null;
        }

        return ClassNames::short($fqcn);
    }
}
