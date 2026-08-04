<?php

declare(strict_types=1);

namespace Innis\CodingStandards\Tests\Rules;

use Innis\CodingStandards\Rules\CollectionOverArray\ArrayUsageCollector;
use Innis\CodingStandards\Rules\CollectionOverArray\CollectionElementCollector;
use Innis\CodingStandards\Rules\CollectionOverArray\CollectionOverArrayRule;
use Override;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<CollectionOverArrayRule>
 */
final class CollectionOverArrayRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new CollectionOverArrayRule();
    }

    #[Override]
    protected function getCollectors(): array
    {
        return [
            new CollectionElementCollector(),
            new ArrayUsageCollector(),
        ];
    }

    public function testFlagsArrayOfCollectedElementAtBoundaries(): void
    {
        $this->analyse([
            __DIR__.'/../data/CollectionOverArray/Event.php',
            __DIR__.'/../data/CollectionOverArray/EventCollection.php',
            __DIR__.'/../data/CollectionOverArray/EventService.php',
        ], [
            ['the return of process() is an array of Event; pass the typed collection EventCollection across the boundary, not a generic array.', 16],
            ['parameter $events of process() is an array of Event; pass the typed collection EventCollection across the boundary, not a generic array.', 17],
        ]);
    }

    public function testAcceptsCollectionAndUncollectedArray(): void
    {
        $this->analyse([
            __DIR__.'/../data/CollectionOverArray/Event.php',
            __DIR__.'/../data/CollectionOverArray/EventCollection.php',
            __DIR__.'/../data/CollectionOverArray/Valid.php',
        ], []);
    }
}
