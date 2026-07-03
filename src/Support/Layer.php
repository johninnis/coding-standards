<?php

declare(strict_types=1);

namespace Innis\CodingStandards\Support;

/**
 * Clean-architecture layer arithmetic over fully-qualified names.
 *
 * A symbol's layer is the first Domain/Application/Infrastructure/Presentation
 * segment in its namespace; dependencies must point strictly inward.
 */
final class Layer
{
    public const string DOMAIN = 'Domain';
    public const string APPLICATION = 'Application';
    public const string INFRASTRUCTURE = 'Infrastructure';
    public const string PRESENTATION = 'Presentation';

    private const array ALL = [self::DOMAIN, self::APPLICATION, self::INFRASTRUCTURE, self::PRESENTATION];

    private const array ALLOWED_DEPENDENCIES = [
        self::DOMAIN => [self::DOMAIN],
        self::APPLICATION => [self::APPLICATION, self::DOMAIN],
        self::INFRASTRUCTURE => [self::INFRASTRUCTURE, self::APPLICATION, self::DOMAIN],
        self::PRESENTATION => [self::PRESENTATION, self::APPLICATION, self::INFRASTRUCTURE, self::DOMAIN],
    ];

    public static function of(string $fullyQualifiedName): ?string
    {
        foreach (explode('\\', $fullyQualifiedName) as $segment) {
            if (in_array($segment, self::ALL, true)) {
                return $segment;
            }
        }

        return null;
    }

    public static function allows(string $from, string $to): bool
    {
        return in_array($to, self::ALLOWED_DEPENDENCIES[$from] ?? [], true);
    }
}
