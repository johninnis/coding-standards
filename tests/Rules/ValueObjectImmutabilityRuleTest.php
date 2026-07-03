<?php

declare(strict_types=1);

namespace Innis\CodingStandards\Tests\Rules;

use Innis\CodingStandards\Rules\ValueObjectImmutabilityRule;
use Innis\CodingStandards\Support\DeliberateFence;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<ValueObjectImmutabilityRule>
 */
final class ValueObjectImmutabilityRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new ValueObjectImmutabilityRule(new DeliberateFence());
    }

    public function testFlagsNonFinalValueObject(): void
    {
        $this->analyse([__DIR__.'/../data/ValueObject/NotFinal.php'], [
            ["Concrete value object Address must be 'final'.", 7],
        ]);
    }

    public function testFlagsNonReadonlyValueObject(): void
    {
        $this->analyse([__DIR__.'/../data/ValueObject/NotReadonly.php'], [
            ["Value object Money must be a 'final readonly class'.", 7],
        ]);
    }

    public function testWarnsOnPropertyLevelReadonly(): void
    {
        $this->analyse([__DIR__.'/../data/ValueObject/PropertyReadonly.php'], [
            [
                "Value object Token uses property-level readonly, not a 'readonly class'; confirm a documented reason (e.g. memory zeroing).",
                7,
                'Add a `// Deliberate: …` comment or ADR-NNNN reference to record the reason.',
            ],
        ]);
    }

    public function testAcceptsFinalReadonlyAndExemptions(): void
    {
        $this->analyse([__DIR__.'/../data/ValueObject/Valid.php'], []);
        $this->analyse([__DIR__.'/../data/ValueObject/Exempt.php'], []);
    }

    public function testIgnoresTestClassInMirroredNamespace(): void
    {
        $this->analyse([__DIR__.'/../data/ValueObject/InTestNamespace.php'], []);
    }
}
