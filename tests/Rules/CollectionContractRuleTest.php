<?php

declare(strict_types=1);

namespace Innis\CodingStandards\Tests\Rules;

use Innis\CodingStandards\Rules\CollectionContractRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<CollectionContractRule>
 */
final class CollectionContractRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new CollectionContractRule();
    }

    public function testFlagsNonFinalCollectionMissingContracts(): void
    {
        $this->analyse([__DIR__.'/../data/CollectionContract/NotFinal.php'], [
            ["Typed collection EventCollection must be 'final'.", 7],
            ['Typed collection EventCollection must implement IteratorAggregate.', 7],
            ['Typed collection EventCollection must implement Countable.', 7],
        ]);
    }

    public function testAcceptsFinalCollectionWithContracts(): void
    {
        $this->analyse([__DIR__.'/../data/CollectionContract/Valid.php'], []);
    }
}
