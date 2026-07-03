<?php

declare(strict_types=1);

namespace Innis\CodingStandards\Rules\CollectionOverArray;

use Innis\CodingStandards\Support\ClassNames;
use Innis\CodingStandards\Support\TypedCollections;
use Override;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Collectors\Collector;
use PHPStan\Node\InClassNode;

/**
 * Collects the element short name of every typed collection in the package — `EventCollection`
 * wraps `Event`. The name is the source of truth for the element type (the ecosystem names a
 * collection `<Element>Collection`), which is what lets the rule know a collection exists for a
 * given element without resolving the `@extends TypedCollection<…>` generic.
 *
 * @implements Collector<InClassNode, string>
 */
final class CollectionElementCollector implements Collector
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
        if (ClassNames::isTestNamespace(ClassNames::namespace($fqcn))) {
            return null;
        }

        $shortName = ClassNames::short($fqcn);
        if (TypedCollections::BASE === $shortName || !str_ends_with($shortName, 'Collection')) {
            return null;
        }

        return substr($shortName, 0, -strlen('Collection'));
    }
}
