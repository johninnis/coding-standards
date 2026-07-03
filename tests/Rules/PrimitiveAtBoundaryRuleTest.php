<?php

declare(strict_types=1);

namespace Innis\CodingStandards\Tests\Rules;

use Innis\CodingStandards\Rules\PrimitiveAtBoundary\PrimitiveAtBoundaryRule;
use Innis\CodingStandards\Rules\PrimitiveAtBoundary\PrimitiveUsageCollector;
use Innis\CodingStandards\Rules\PrimitiveAtBoundary\ValueObjectConceptCollector;
use Innis\CodingStandards\Support\DeliberateFence;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<PrimitiveAtBoundaryRule>
 */
final class PrimitiveAtBoundaryRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new PrimitiveAtBoundaryRule();
    }

    protected function getCollectors(): array
    {
        return [
            new ValueObjectConceptCollector(),
            new PrimitiveUsageCollector(new DeliberateFence()),
        ];
    }

    public function testFlagsPrimitivesNamedForAnExistingValueObject(): void
    {
        $this->analyse([
            __DIR__.'/../data/PrimitiveAtBoundary/PublicKey.php',
            __DIR__.'/../data/PrimitiveAtBoundary/Leaky.php',
        ], [
            ['property $publicKey is a primitive named for the PublicKey value object; parse it to PublicKey at the boundary and thread the value object through.', 9],
            ['parameter $publicKey of handle() is a primitive named for the PublicKey value object; parse it to PublicKey at the boundary and thread the value object through.', 11],
        ]);
    }

    public function testAcceptsValueObjectTypedUnmatchedAndCommonWordPrimitives(): void
    {
        $this->analyse([
            __DIR__.'/../data/PrimitiveAtBoundary/PublicKey.php',
            __DIR__.'/../data/PrimitiveAtBoundary/Message.php',
            __DIR__.'/../data/PrimitiveAtBoundary/Clean.php',
        ], []);
    }

    public function testFenceSilencesAPrimitiveBoundary(): void
    {
        $this->analyse([
            __DIR__.'/../data/PrimitiveAtBoundary/PublicKey.php',
            __DIR__.'/../data/PrimitiveAtBoundary/Fenced.php',
        ], []);
    }
}
