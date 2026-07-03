<?php

declare(strict_types=1);

namespace Innis\CodingStandards\Tests\Rules;

use Innis\CodingStandards\Rules\NoSingletonRule;
use Innis\CodingStandards\Support\DeliberateFence;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<NoSingletonRule>
 */
final class NoSingletonRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new NoSingletonRule(new DeliberateFence());
    }

    public function testFlagsStaticInstanceAndAccessor(): void
    {
        $this->analyse([__DIR__.'/../data/Singleton/Singleton.php'], [
            ['Class Config holds a static instance of itself in $instance; this is a singleton — depend on an injected interface, not a global access point.', 9],
            ['Class Config exposes a static getInstance() accessor; a singleton/service locator hides its dependency — inject it through an interface instead.', 11],
        ]);
    }

    public function testAcceptsNamedConstructorAndMemoisationCache(): void
    {
        $this->analyse([__DIR__.'/../data/Singleton/Valid.php'], []);
    }
}
