<?php

declare(strict_types=1);

namespace Innis\CodingStandards\Rules;

use Innis\CodingStandards\Support\ClassNames;
use Innis\CodingStandards\Support\TypedCollections;
use Override;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * A typed collection (`*Collection`, or a class extending `TypedCollection`) must live in
 * a `Collection/` namespace, and `Collection/` stays flat — no sub-grouping by concept.
 *
 * @implements Rule<InClassNode>
 */
final class CollectionPlacementRule implements Rule
{
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
        $namespace = ClassNames::namespace($fqcn);
        if (ClassNames::isTestNamespace($namespace)) {
            return [];
        }
        $name = ClassNames::short($fqcn);

        $inCollectionNamespace = ClassNames::hasSegment($namespace, 'Collection');
        $namedCollection = str_ends_with($name, 'Collection');
        $extendsTypedCollection = TypedCollections::extendsBase($original);

        $errors = [];

        if (($namedCollection || $extendsTypedCollection) && !$inCollectionNamespace) {
            $errors[] = RuleErrorBuilder::message("Typed collection {$name} must live in a Collection/ namespace.")
                ->identifier('innis.collectionPlacement')
                ->line($original->getStartLine())
                ->build();
        }

        if (1 === preg_match('~\\\\Collection\\\\\w+~', $namespace) || 1 === preg_match('~^Collection\\\\\w+~', $namespace)) {
            $errors[] = RuleErrorBuilder::message('Collection/ must stay flat; do not sub-group it by concept.')
                ->identifier('innis.collectionFlat')
                ->line($original->getStartLine())
                ->build();
        }

        return $errors;
    }
}
