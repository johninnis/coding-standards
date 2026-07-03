<?php

declare(strict_types=1);

namespace Innis\CodingStandards\Tests\Rules;

use Innis\CodingStandards\Rules\EqualsSelfRule;
use Innis\CodingStandards\Support\DeliberateFence;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<EqualsSelfRule>
 */
final class EqualsSelfRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new EqualsSelfRule(new DeliberateFence());
    }

    public function testFlagsWidenedAndUntypedEqualityParameters(): void
    {
        $this->analyse([__DIR__.'/../data/Equals/WidenedEquals.php'], [
            ['PublicKey::equals() accepts object; a value object compares only against its own type — type the parameter as self.', 13],
            ['PublicKey::isEqualTo() accepts an untyped parameter; a value object compares only against its own type — type the parameter as self.', 18],
        ]);
    }

    public function testAcceptsSelfTypedEquality(): void
    {
        $this->analyse([__DIR__.'/../data/Equals/Valid.php'], []);
    }

    public function testIgnoresClassInSiblingNamespaceSharingOnlyAPrefix(): void
    {
        $this->analyse([__DIR__.'/../data/Equals/SiblingNamespace.php'], []);
    }
}
