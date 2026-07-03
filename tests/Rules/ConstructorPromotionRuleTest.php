<?php

declare(strict_types=1);

namespace Innis\CodingStandards\Tests\Rules;

use Innis\CodingStandards\Rules\ConstructorPromotionRule;
use Innis\CodingStandards\Support\DeliberateFence;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<ConstructorPromotionRule>
 */
final class ConstructorPromotionRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new ConstructorPromotionRule(new DeliberateFence());
    }

    public function testFlagsDeclareThenAssign(): void
    {
        $this->analyse([__DIR__.'/../data/Promotion/DeclareAssign.php'], [
            ['Promote constructor property $x instead of declaring the field and assigning the parameter in the body.', 15],
            ['Promote constructor property $y instead of declaring the field and assigning the parameter in the body.', 16],
        ]);
    }

    public function testAcceptsPromotedAndTransformingConstructors(): void
    {
        $this->analyse([__DIR__.'/../data/Promotion/Valid.php'], []);
    }
}
