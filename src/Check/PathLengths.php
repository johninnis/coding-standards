<?php

declare(strict_types=1);

namespace Innis\CodingStandards\Check;

/**
 * The repository paths with a component longer than a registry accepts. JSR refuses any path
 * component over 95 characters, and the limit holds for every Innis repository so that an ADR
 * filename, or any other, means the same in a PHP package as in its TypeScript port.
 */
final class PathLengths
{
    public const int MAX_COMPONENT_LENGTH = 95;

    /**
     * @param list<string> $paths repository-relative paths, `/`-separated
     *
     * @return list<string> the paths holding a component longer than the limit, in input order
     */
    public static function overlong(array $paths, int $limit = self::MAX_COMPONENT_LENGTH): array
    {
        return array_values(array_filter(
            $paths,
            static fn (string $path): bool => [] !== array_filter(
                explode('/', $path),
                static fn (string $component): bool => mb_strlen($component) > $limit,
            ),
        ));
    }
}
