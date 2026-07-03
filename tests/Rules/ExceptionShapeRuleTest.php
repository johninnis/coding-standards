<?php

declare(strict_types=1);

namespace Innis\CodingStandards\Tests\Rules;

use Innis\CodingStandards\Rules\ExceptionShapeRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<ExceptionShapeRule>
 */
final class ExceptionShapeRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new ExceptionShapeRule();
    }

    public function testFlagsExceptionOutsideExceptionNamespace(): void
    {
        $this->analyse([__DIR__.'/../data/Exception/OutsideNamespace.php'], [
            ['Fault OrderException must live in an Exception/ namespace.', 7],
        ]);
    }

    public function testFlagsNonFinalNonAbstractException(): void
    {
        $this->analyse([__DIR__.'/../data/Exception/NotFinalOrAbstract.php'], [
            ["Exception OrderFailedException must be 'final' (leaf) or 'abstract' (base).", 7],
        ]);
    }

    public function testAcceptsFinalExceptionInExceptionNamespace(): void
    {
        $this->analyse([__DIR__.'/../data/Exception/Valid.php'], []);
    }
}
