<?php

declare(strict_types=1);

namespace Innis\CodingStandards\Tests\Rules;

use Innis\CodingStandards\Rules\OverrideAttributeRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<OverrideAttributeRule>
 */
final class OverrideAttributeRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new OverrideAttributeRule();
    }

    public function testFlagsMissingOverrideAttribute(): void
    {
        $this->analyse([__DIR__.'/../data/Override/Missing.php'], [
            ['__toString() implements or overrides an inherited method and must carry #[\Override].', 13],
        ]);
    }

    public function testAcceptsPresentOverrideAndNonContractMethod(): void
    {
        $this->analyse([__DIR__.'/../data/Override/Valid.php'], []);
    }

    public function testInTestCodeEnforcesFirstPartyContractsButSkipsBuiltins(): void
    {
        $this->analyse([__DIR__.'/../data/Override/TestDoubles.php'], [
            ['greet() implements or overrides an inherited method and must carry #[\Override].', 11],
        ]);
    }
}
