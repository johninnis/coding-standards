<?php

declare(strict_types=1);

namespace Innis\CodingStandards\Tests\Rules;

use Innis\CodingStandards\Rules\ValueObjectAccessorsRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<ValueObjectAccessorsRule>
 */
final class ValueObjectAccessorsRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new ValueObjectAccessorsRule();
    }

    public function testFlagsHooksAndAsymmetryOnValueObject(): void
    {
        $this->analyse([__DIR__.'/../data/Accessors/Hooked.php'], [
            ['Value object Temperature::$label uses a property hook; expose a computed or interface-bound read through a getX() method.', 9],
            ['Value object Temperature::$celsius uses asymmetric visibility; a value object exposes reads through a getX() method, not private(set).', 13],
        ]);
    }

    public function testFlagsAsymmetryOnEntityState(): void
    {
        $this->analyse([__DIR__.'/../data/Accessors/EntityState.php'], [
            ['Entity Order::$status uses asymmetric visibility; lifecycle state stays behind a getX() method mutated through named transformations, not a private(set) property.', 9],
            ['Entity Account::$owner uses asymmetric visibility; lifecycle state stays behind a getX() method mutated through named transformations, not a private(set) property.', 19],
        ]);
    }

    public function testAcceptsPlainValueObject(): void
    {
        $this->analyse([__DIR__.'/../data/Accessors/Valid.php'], []);
    }
}
