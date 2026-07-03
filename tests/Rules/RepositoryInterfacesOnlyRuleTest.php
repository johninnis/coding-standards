<?php

declare(strict_types=1);

namespace Innis\CodingStandards\Tests\Rules;

use Innis\CodingStandards\Rules\RepositoryInterfacesOnlyRule;
use Innis\CodingStandards\Support\DeliberateFence;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<RepositoryInterfacesOnlyRule>
 */
final class RepositoryInterfacesOnlyRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new RepositoryInterfacesOnlyRule(new DeliberateFence());
    }

    public function testFlagsConcreteClassInRepositoryNamespace(): void
    {
        $this->analyse([__DIR__.'/../data/Repository/Concrete.php'], [
            ['Domain/Repository holds interfaces only; OrderRepository is a class.', 7],
        ]);
    }

    public function testAcceptsInterfaceInRepositoryNamespace(): void
    {
        $this->analyse([__DIR__.'/../data/Repository/ValidInterface.php'], []);
    }
}
