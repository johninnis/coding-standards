<?php

declare(strict_types=1);

namespace Innis\CodingStandards\Tests\Rules;

use Innis\CodingStandards\Rules\DomainPurityRule;
use Innis\CodingStandards\Support\DeliberateFence;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<DomainPurityRule>
 */
final class DomainPurityRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new DomainPurityRule(new DeliberateFence());
    }

    public function testFlagsImpureCallsInDomain(): void
    {
        $this->analyse([__DIR__.'/../data/Purity/Impure.php'], [
            ['Domain must stay pure: time() reads the clock; move it behind a port in Infrastructure.', 11],
            ['Domain must stay pure: rand() uses randomness; move it behind a port in Infrastructure.', 12],
            ['Domain must stay pure: file_get_contents() performs I/O; move it behind a port in Infrastructure.', 13],
            ['Domain must stay pure: constructing DateTimeImmutable() with no argument reads the clock; inject the time behind a port.', 14],
            ['Domain must stay pure: $_GET reads the request; pass the value in rather than reading a superglobal.', 15],
        ]);
    }

    public function testAcceptsPureDomainCode(): void
    {
        $this->analyse([__DIR__.'/../data/Purity/Pure.php'], []);
    }

    public function testIgnoresImpureCallsOutsideDomain(): void
    {
        $this->analyse([__DIR__.'/../data/Purity/OutsideDomain.php'], []);
    }

    public function testFenceSilencesPerClassNotItsSiblings(): void
    {
        $this->analyse([__DIR__.'/../data/Purity/Fenced.php'], [
            ['Domain must stay pure: time() reads the clock; move it behind a port in Infrastructure.', 20],
        ]);
    }

    public function testFenceSilencesPerMethodNotItsSiblings(): void
    {
        $this->analyse([__DIR__.'/../data/Purity/FencedMethod.php'], [
            ['Domain must stay pure: time() reads the clock; move it behind a port in Infrastructure.', 17],
        ]);
    }
}
