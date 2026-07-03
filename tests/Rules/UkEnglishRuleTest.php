<?php

declare(strict_types=1);

namespace Innis\CodingStandards\Tests\Rules;

use Innis\CodingStandards\Rules\UkEnglishRule;
use Innis\CodingStandards\Support\DeliberateFence;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<UkEnglishRule>
 */
final class UkEnglishRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new UkEnglishRule(new DeliberateFence());
    }

    public function testFlagsUsSpellingsInIdentifiers(): void
    {
        $this->analyse([__DIR__.'/../data/UkEnglish/UsSpellings.php'], [
            ['Identifier EventSerializer uses a US spelling; prefer EventSerialiser (UK English).', 7],
            ['Identifier MAX_COLOR uses a US spelling; prefer MAX_COLOUR (UK English).', 9],
            ['Identifier normalize uses a US spelling; prefer normalise (UK English).', 11],
            ['Identifier color uses a US spelling; prefer colour (UK English).', 12],
        ]);
    }

    public function testAcceptsUkSpellingsAndHomographs(): void
    {
        $this->analyse([__DIR__.'/../data/UkEnglish/Valid.php'], []);
    }

    public function testFenceSilencesAMethodNameAndItsParametersNotItsSiblings(): void
    {
        $this->analyse([__DIR__.'/../data/UkEnglish/Fenced.php'], [
            ['Identifier color uses a US spelling; prefer colour (UK English).', 15],
        ]);
    }
}
