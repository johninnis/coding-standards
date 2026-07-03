<?php

declare(strict_types=1);

namespace Innis\CodingStandards\Tests\Rules;

use Innis\CodingStandards\Rules\CollectionPlacementRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<CollectionPlacementRule>
 */
final class CollectionPlacementRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new CollectionPlacementRule();
    }

    public function testFlagsCollectionOutsideCollectionNamespace(): void
    {
        $this->analyse([__DIR__.'/../data/Collection/EventCollection.php'], [
            ['Typed collection EventCollection must live in a Collection/ namespace.', 7],
        ]);
    }

    public function testFlagsSubGroupedCollection(): void
    {
        $this->analyse([__DIR__.'/../data/Collection/SubGrouped.php'], [
            ['Collection/ must stay flat; do not sub-group it by concept.', 7],
        ]);
    }

    public function testAcceptsFlatCollectionInCollectionNamespace(): void
    {
        $this->analyse([__DIR__.'/../data/Collection/Valid.php'], []);
    }
}
