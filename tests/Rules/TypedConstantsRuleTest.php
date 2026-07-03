<?php

declare(strict_types=1);

namespace Innis\CodingStandards\Tests\Rules;

use Innis\CodingStandards\Rules\TypedConstantsRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<TypedConstantsRule>
 */
final class TypedConstantsRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new TypedConstantsRule();
    }

    public function testFlagsUntypedConstant(): void
    {
        $this->analyse([__DIR__.'/../data/Constants/Untyped.php'], [
            ['Constant MAX must declare a type.', 11],
        ]);
    }
}
