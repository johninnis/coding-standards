<?php

declare(strict_types=1);

namespace Innis\CodingStandards\Rules\CollectionOverArray;

use Override;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\CollectedDataNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Whole-package check: a public signature that passes an array of an element for which a typed
 * collection exists should pass the collection, so the element type is guaranteed by
 * construction rather than re-asserted at every boundary. The rule fires only when a
 * `<Element>Collection` actually exists, so a bare `array` or an array of an uncollected type
 * is never flagged.
 *
 * @implements Rule<CollectedDataNode>
 */
final class CollectionOverArrayRule implements Rule
{
    #[Override]
    public function getNodeType(): string
    {
        return CollectedDataNode::class;
    }

    #[Override]
    public function processNode(Node $node, Scope $scope): array
    {
        $elements = $this->collectionElements($node);
        if ([] === $elements) {
            return [];
        }

        $errors = [];
        foreach ($node->get(ArrayUsageCollector::class) as $file => $classUsages) {
            foreach ($classUsages as $usages) {
                foreach ($usages as $usage) {
                    if (!isset($elements[$usage['element']])) {
                        continue;
                    }
                    $errors[] = RuleErrorBuilder::message("{$usage['where']} is an array of {$usage['element']}; pass the typed collection {$usage['element']}Collection across the boundary, not a generic array.")
                        ->identifier('innis.collectionOverArray')
                        ->file($file)
                        ->line($usage['line'])
                        ->build();
                }
            }
        }

        return $errors;
    }

    /**
     * @return array<string, true>
     */
    private function collectionElements(CollectedDataNode $node): array
    {
        $elements = [];
        foreach ($node->get(CollectionElementCollector::class) as $names) {
            foreach ($names as $name) {
                $elements[$name] = true;
            }
        }

        return $elements;
    }
}
