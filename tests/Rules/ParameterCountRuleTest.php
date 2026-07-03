<?php

declare(strict_types=1);

namespace Innis\CodingStandards\Tests\Rules;

use Innis\CodingStandards\Rules\ParameterCountRule;
use Innis\CodingStandards\Support\DeliberateFence;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<ParameterCountRule>
 */
final class ParameterCountRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new ParameterCountRule(new DeliberateFence());
    }

    public function testFlagsMoreThanThreeParameters(): void
    {
        $this->analyse([__DIR__.'/../data/Params/TooMany.php'], [
            ['build() takes 4 parameters; more than 3 is a design signal — decompose the unit rather than bundling arguments into a parameter object.', 7],
            ['handle() takes 4 parameters; more than 3 is a design signal — decompose the unit rather than bundling arguments into a parameter object.', 14],
        ]);
    }

    public function testFenceSilencesPerMethodNotItsSiblings(): void
    {
        $this->analyse([__DIR__.'/../data/Params/Fenced.php'], [
            ['unfenced() takes 4 parameters; more than 3 is a design signal — decompose the unit rather than bundling arguments into a parameter object.', 14],
        ]);
    }
}
