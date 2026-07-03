<?php

declare(strict_types=1);

namespace Innis\CodingStandards\Support;

use PhpParser\Node\Stmt\ClassMethod;

/** Pure helpers for reading a method's declared attributes by short name. */
final class Attributes
{
    public static function isPresent(ClassMethod $method, string $shortName): bool
    {
        $needle = strtolower($shortName);
        foreach ($method->attrGroups as $group) {
            foreach ($group->attrs as $attribute) {
                if ($needle === strtolower(ClassNames::short($attribute->name->toString()))) {
                    return true;
                }
            }
        }

        return false;
    }
}
