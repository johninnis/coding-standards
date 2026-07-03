<?php

declare(strict_types=1);

namespace Innis\CodingStandards\Support;

use PhpParser\Node\Stmt\Class_;

/**
 * Recognises the ecosystem's typed-collection shape — the abstract `TypedCollection` base and its
 * `<Element>Collection` leaves — so the collection rules share one definition instead of each
 * re-deriving "is this a typed collection?" from the name and the `extends` clause.
 */
final class TypedCollections
{
    public const string BASE = 'TypedCollection';

    public static function extendsBase(Class_ $class): bool
    {
        return null !== $class->extends && self::BASE === ClassNames::short($class->extends->toString());
    }

    /**
     * A concrete typed collection: a leaf named `<Element>Collection` or extending the base, but
     * never the abstract base itself.
     */
    public static function isConcrete(Class_ $class, string $shortName): bool
    {
        return self::BASE !== $shortName
            && (str_ends_with($shortName, 'Collection') || self::extendsBase($class));
    }
}
