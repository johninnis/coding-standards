<?php

declare(strict_types=1);

namespace Innis\CodingStandards\Tests\Rules;

use Innis\CodingStandards\Rules\NoDomainRepositoryRule;
use Innis\CodingStandards\Support\DeliberateFence;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<NoDomainRepositoryRule>
 */
final class NoDomainRepositoryRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new NoDomainRepositoryRule(new DeliberateFence());
    }

    public function testFlagsInterfaceInRepositoryNamespace(): void
    {
        $this->analyse([__DIR__.'/../data/Repository/ValidInterface.php'], [
            ['OrderRepositoryInterface is under Domain/Repository; a persistence store is a driven port — file its interface in Application/Port, not Domain.', 7],
        ]);
    }

    public function testFlagsConcreteClassInRepositoryNamespace(): void
    {
        $this->analyse([__DIR__.'/../data/Repository/Concrete.php'], [
            ['OrderRepository is under Domain/Repository; a persistence store is a driven port — file its interface in Application/Port, not Domain.', 7],
        ]);
    }

    public function testAcceptsAnAdrFencedDeparture(): void
    {
        $this->analyse([__DIR__.'/../data/Repository/Fenced.php'], []);
    }
}
