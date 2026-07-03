<?php

declare(strict_types=1);

namespace Innis\CodingStandards\Tests\Rules;

use Innis\CodingStandards\Rules\FailureNotThrowableRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<FailureNotThrowableRule>
 */
final class FailureNotThrowableRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new FailureNotThrowableRule();
    }

    public function testFlagsThrowableFailureButNotValueFailure(): void
    {
        $this->analyse([__DIR__.'/../data/Failure/Failures.php'], [
            ['PaymentFailure is a returned outcome value and must not be throwable; a thrown fault uses the *Exception suffix.', 7],
        ]);
    }
}
