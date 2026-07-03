<?php

declare(strict_types=1);

namespace Innis\CodingStandards\Tests\Rules;

use Innis\CodingStandards\Rules\NoEmojisRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<NoEmojisRule>
 */
final class NoEmojisRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new NoEmojisRule();
    }

    public function testFlagsEmojisInCodeAndComments(): void
    {
        $this->analyse([__DIR__.'/../data/Emoji/HasEmoji.php'], [
            ['Emoji found; no emojis are permitted anywhere (code, comments, or output).', 9],
            ['Emoji found; no emojis are permitted anywhere (code, comments, or output).', 12],
        ]);
    }

    public function testAcceptsEmojiFreeSource(): void
    {
        $this->analyse([__DIR__.'/../data/Emoji/Valid.php'], []);
    }
}
