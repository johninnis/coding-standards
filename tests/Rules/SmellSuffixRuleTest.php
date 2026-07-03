<?php

declare(strict_types=1);

namespace Innis\CodingStandards\Tests\Rules;

use Innis\CodingStandards\Rules\SmellSuffixRule;
use Innis\CodingStandards\Support\DeliberateFence;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<SmellSuffixRule>
 */
final class SmellSuffixRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new SmellSuffixRule(new DeliberateFence());
    }

    public function testFlagsCatchAllSuffixes(): void
    {
        $this->analyse([__DIR__.'/../data/Smell/Smells.php'], [
            ['PaymentManager: the *Manager suffix is a catch-all smell unless it is a genuine lifecycle/registry.', 7],
            ['OrderService: a bare *Service suffix is a catch-all smell; name the class for what it does.', 11],
            ['LegacyAdapter: reserve the *Adapter suffix for a true GoF adapter; name an implementation for what it does.', 15],
        ]);
    }

    public function testFenceSilencesASuffix(): void
    {
        $this->analyse([__DIR__.'/../data/Smell/Fenced.php'], []);
    }
}
