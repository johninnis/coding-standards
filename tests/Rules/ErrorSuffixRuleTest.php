<?php

declare(strict_types=1);

namespace Innis\CodingStandards\Tests\Rules;

use Innis\CodingStandards\Rules\ErrorSuffixRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<ErrorSuffixRule>
 */
final class ErrorSuffixRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new ErrorSuffixRule();
    }

    public function testFlagsNonThrowableErrorSuffix(): void
    {
        $this->analyse([__DIR__.'/../data/ErrorSuffix/NonThrowable.php'], [
            ['ValidationError is not throwable; a returned outcome value uses the *Failure suffix, not *Error (\Error is a Throwable).', 7],
        ]);
    }

    public function testAcceptsFailureAndThrowableError(): void
    {
        $this->analyse([__DIR__.'/../data/ErrorSuffix/Valid.php'], []);
    }
}
