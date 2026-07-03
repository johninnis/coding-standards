<?php

declare(strict_types=1);

namespace Innis\CodingStandards\Tests\Rules;

use Innis\CodingStandards\Rules\ValueParserConventionRule;
use Innis\CodingStandards\Support\DeliberateFence;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<ValueParserConventionRule>
 */
final class ValueParserConventionRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new ValueParserConventionRule(new DeliberateFence());
    }

    public function testFlagsNonNullableThrowingTryFromAndNullableFrom(): void
    {
        $this->analyse([__DIR__.'/../data/Parser/Violations.php'], [
            ['Untrusted-input parser tryFromHex() returns the constructed type non-nullably; a tryFrom parser returns ?self (or a *Failure) so the caller must handle bad input.', 13],
            ['Untrusted-input parser tryFromHex() throws; a tryFrom parser reports bad input as null, it does not throw a fault.', 13],
            ['Total constructor fromRaw() returns nullable; a parser that reports failure as a value belongs under the tryFrom name — rename it tryFromRaw() (a from constructor is total and throws to assert an invariant instead).', 22],
        ]);
    }

    public function testAcceptsNullableTryFromTotalFromAndFencedDeparture(): void
    {
        $this->analyse([__DIR__.'/../data/Parser/Valid.php'], []);
    }
}
