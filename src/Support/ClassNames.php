<?php

declare(strict_types=1);

namespace Innis\CodingStandards\Support;

/** Pure helpers for splitting a fully-qualified name into its parts. */
final class ClassNames
{
    public static function short(string $fullyQualifiedName): string
    {
        $parts = explode('\\', ltrim($fullyQualifiedName, '\\'));

        return end($parts) ?: $fullyQualifiedName;
    }

    public static function namespace(string $fullyQualifiedName): string
    {
        $name = ltrim($fullyQualifiedName, '\\');
        $position = strrpos($name, '\\');

        return false === $position ? '' : substr($name, 0, $position);
    }

    /**
     * True when the namespace has a `Tests` segment — the ecosystem's autoload-dev marker.
     * The architecture and placement rules govern production code; test namespaces mirror the
     * production tree (e.g. `…\Tests\Unit\Domain\ValueObject`) and must not be judged by them.
     */
    public static function isTestNamespace(string $namespace): bool
    {
        return in_array('Tests', explode('\\', $namespace), true);
    }

    /**
     * True when the namespace carries the given path as whole segments, independent of the root:
     * `ValueObject` matches `Acme\Domain\ValueObject`, the nested `Acme\Domain\ValueObject\Reference`,
     * and a root-level `ValueObject\Money`, but not the sibling `Acme\Domain\ValueObjectSupport`. A
     * multi-segment path (`Application\Port`) matches only where its segments are consecutive. This is
     * the one place segment membership is decided, so every rule agrees on where a layer or kind begins.
     */
    public static function hasSegment(string $namespace, string $segmentPath): bool
    {
        return 1 === preg_match('~(^|\\\\)'.preg_quote($segmentPath, '~').'($|\\\\)~', $namespace);
    }

    /**
     * A case- and separator-insensitive key for matching a name against a concept, so a parameter
     * `$public_key` or `$publicKey` and a value object `PublicKey` compare equal.
     */
    public static function conceptKey(string $name): string
    {
        return str_replace('_', '', strtolower(ltrim($name, '$')));
    }
}
