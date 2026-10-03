<?php

declare(strict_types=1);

namespace Innis\CodingStandards\Tests\Check;

use Innis\CodingStandards\Check\PathLengths;
use PHPUnit\Framework\TestCase;

final class PathLengthsTest extends TestCase
{
    public function testAcceptsAComponentAtTheLimit(): void
    {
        self::assertSame([], PathLengths::overlong(['docs/adr/'.str_repeat('a', 92).'.md']));
    }

    public function testReportsAFileNameOverTheLimit(): void
    {
        $path = 'docs/adr/'.str_repeat('a', 93).'.md';

        self::assertSame([$path], PathLengths::overlong([$path, 'README.md']));
    }

    public function testReportsADirectoryNameOverTheLimit(): void
    {
        $path = str_repeat('d', 96).'/file.php';

        self::assertSame([$path], PathLengths::overlong([$path]));
    }

    public function testCountsCharactersNotBytes(): void
    {
        self::assertSame([], PathLengths::overlong([str_repeat('é', 95)]));
    }

    public function testTakesAnExplicitLimit(): void
    {
        self::assertSame(['abcd'], PathLengths::overlong(['abc', 'abcd'], 3));
    }
}
