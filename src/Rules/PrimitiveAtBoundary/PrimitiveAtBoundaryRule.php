<?php

declare(strict_types=1);

namespace Innis\CodingStandards\Rules\PrimitiveAtBoundary;

use Innis\CodingStandards\Support\ClassNames;
use Override;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\CollectedDataNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Whole-package check: a `string`/`int` parameter or property in a Domain or Application class
 * whose name matches an existing value object is a bare primitive that should be the value object
 * instead — parsed at the boundary and threaded through. The rule fires only when a value object of
 * that name exists, so a primitive with no domain type to become is never flagged. See ADR-0010.
 *
 * @implements Rule<CollectedDataNode>
 */
final class PrimitiveAtBoundaryRule implements Rule
{
    private const int MIN_CONCEPT_LENGTH = 5;

    /**
     * Concept keys that double as ordinary field names — a value object of this name matches too
     * broadly to flag (a `string $message` is a text payload far more often than the `Message`
     * envelope). Curated, like the length floor, to trade recall for precision. See ADR-0010.
     */
    private const array COMMON_WORDS = [
        'message', 'content', 'value', 'values', 'data', 'name', 'type', 'text', 'body', 'payload',
        'result', 'response', 'request', 'item', 'element', 'field', 'entry', 'record', 'state',
        'status', 'reason', 'error', 'label', 'title', 'description', 'address', 'input', 'output',
    ];

    #[Override]
    public function getNodeType(): string
    {
        return CollectedDataNode::class;
    }

    #[Override]
    public function processNode(Node $node, Scope $scope): array
    {
        $concepts = $this->concepts($node);
        if ([] === $concepts) {
            return [];
        }

        $errors = [];
        foreach ($node->get(PrimitiveUsageCollector::class) as $file => $classUsages) {
            foreach ($classUsages as $usages) {
                foreach ($usages as $usage) {
                    $concept = $concepts[$usage['key']] ?? null;
                    if (null === $concept || $usage['key'] === $usage['enclosing']) {
                        continue;
                    }
                    $errors[] = RuleErrorBuilder::message("{$usage['where']} is a primitive named for the {$concept} value object; parse it to {$concept} at the boundary and thread the value object through.")
                        ->identifier('innis.primitiveAtBoundary')
                        ->file($file)
                        ->line($usage['line'])
                        ->build();
                }
            }
        }

        return $errors;
    }

    /**
     * Concept key => value object short name. Single-word concepts shorter than five characters
     * (`Id`, `Url`, `Name`) are dropped: they match too broadly to flag with confidence.
     *
     * @return array<string, string>
     */
    private function concepts(CollectedDataNode $node): array
    {
        $concepts = [];
        foreach ($node->get(ValueObjectConceptCollector::class) as $names) {
            foreach ($names as $name) {
                $key = ClassNames::conceptKey($name);
                if (strlen($key) >= self::MIN_CONCEPT_LENGTH && !in_array($key, self::COMMON_WORDS, true)) {
                    $concepts[$key] = $name;
                }
            }
        }

        return $concepts;
    }
}
