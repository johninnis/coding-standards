<?php

declare(strict_types=1);

namespace Innis\CodingStandards\Tests\Rules;

use Innis\CodingStandards\Rules\TransformationReturnsSelfRule;
use Innis\CodingStandards\Support\DeliberateFence;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<TransformationReturnsSelfRule>
 */
final class TransformationReturnsSelfRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new TransformationReturnsSelfRule(new DeliberateFence());
    }

    public function testFlagsVoidAndBoolTransformations(): void
    {
        $this->analyse([__DIR__.'/../data/Transformation/VoidMutators.php'], [
            ['Basket::withCount() is a transformation but returns void; an immutable value returns a new instance (self), it does not mutate in place.', 13],
            ['Basket::addItem() is a transformation but returns bool; an immutable value returns a new instance (self), it does not mutate in place.', 18],
        ]);
    }

    public function testAcceptsSelfReturningTransformationAndNonTransformation(): void
    {
        $this->analyse([__DIR__.'/../data/Transformation/Valid.php'], []);
    }
}
