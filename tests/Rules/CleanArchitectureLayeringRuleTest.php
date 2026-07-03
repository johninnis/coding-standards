<?php

declare(strict_types=1);

namespace Innis\CodingStandards\Tests\Rules;

use Innis\CodingStandards\Rules\CleanArchitectureLayeringRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<CleanArchitectureLayeringRule>
 */
final class CleanArchitectureLayeringRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new CleanArchitectureLayeringRule();
    }

    public function testFlagsInwardViolationButAllowsInwardImport(): void
    {
        $this->analyse([__DIR__.'/../data/Layering/Account.php'], [
            ['Domain imports Infrastructure (Innis\Other\Infrastructure\Crypto\Hasher); dependencies must point inward.', 8],
        ]);
    }

    public function testEnforcesLayeringForAnyRootAndIgnoresUnlayeredImports(): void
    {
        $this->analyse([__DIR__.'/../data/Layering/Widget.php'], [
            ['Domain imports Infrastructure (Acme\Toolkit\Infrastructure\Persistence\Store); dependencies must point inward.', 8],
        ]);
    }
}
