<?php

declare(strict_types=1);

namespace Innis\CodingStandards\Tests\Rules;

use Innis\CodingStandards\Rules\NoTraitsRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<NoTraitsRule>
 */
final class NoTraitsRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new NoTraitsRule();
    }

    public function testFlagsTrait(): void
    {
        $this->analyse([__DIR__.'/../data/Trait/Helper.php'], [
            ['Traits are banned ecosystem-wide; share behaviour through an injected collaborator (Helper).', 7],
        ]);
    }
}
