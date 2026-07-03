<?php

declare(strict_types=1);

namespace Innis\CodingStandards\Tests\Rules;

use Innis\CodingStandards\Rules\InterfaceNamingRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<InterfaceNamingRule>
 */
final class InterfaceNamingRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new InterfaceNamingRule();
    }

    public function testFlagsInterfaceWithoutSuffix(): void
    {
        $this->analyse([__DIR__.'/../data/Interface/Interfaces.php'], [
            ["Interface PaymentGateway must end in 'Interface'.", 7],
        ]);
    }
}
