<?php

declare(strict_types=1);

namespace Innis\CodingStandards\Tests\Rules;

use Innis\CodingStandards\Rules\StrictTypesRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<StrictTypesRule>
 */
final class StrictTypesRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new StrictTypesRule();
    }

    public function testFlagsMissingDeclaration(): void
    {
        $this->analyse([__DIR__.'/../data/StrictTypes/Missing.php'], [
            ['Missing declare(strict_types=1).', 1],
        ]);
    }

    public function testAcceptsPresentDeclaration(): void
    {
        $this->analyse([__DIR__.'/../data/StrictTypes/Present.php'], []);
    }
}
