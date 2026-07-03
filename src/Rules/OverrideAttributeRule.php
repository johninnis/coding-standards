<?php

declare(strict_types=1);

namespace Innis\CodingStandards\Rules;

use Innis\CodingStandards\Support\Attributes;
use Innis\CodingStandards\Support\ClassNames;
use Override;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassNode;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Every method that implements an interface method or overrides a parent method carries
 * `#[\Override]`, so the abstract-base / final-leaf hierarchy fails loudly when a signature
 * drifts. Constructors and private methods, which participate in no contract, are exempt.
 *
 * In test code the rule enforces the attribute only for a first-party contract — a method
 * whose declaring interface or parent lives in this codebase, not under `vendor/` and not a
 * PHP built-in. That keeps a hand-written test double honest against the first-party interface
 * it implements, while sparing the pervasive framework overrides (`setUp`, `tearDown`) that
 * carry no drift risk.
 *
 * @implements Rule<InClassNode>
 */
final class OverrideAttributeRule implements Rule
{
    #[Override]
    public function getNodeType(): string
    {
        return InClassNode::class;
    }

    #[Override]
    public function processNode(Node $node, Scope $scope): array
    {
        $classLike = $node->getOriginalNode();
        $reflection = $node->getClassReflection();
        // A test double may be an anonymous class with no Tests segment of its own; see ADR-0003.
        $firstPartyOnly = ClassNames::isTestNamespace(ClassNames::namespace($reflection->getName()))
            || ClassNames::isTestNamespace($scope->getNamespace() ?? '');

        $errors = [];
        foreach ($classLike->getMethods() as $method) {
            $error = $this->methodError($reflection, $method, $firstPartyOnly);
            if (null !== $error) {
                $errors[] = $error;
            }
        }

        return $errors;
    }

    private function methodError(ClassReflection $class, ClassMethod $method, bool $firstPartyOnly): ?IdentifierRuleError
    {
        $name = $method->name->toString();
        if ($method->isAbstract() || $method->isPrivate() || '__construct' === $method->name->toLowerString()) {
            return null;
        }
        if (!$this->overridesContract($class, $name, $firstPartyOnly) || Attributes::isPresent($method, 'Override')) {
            return null;
        }

        return RuleErrorBuilder::message("{$name}() implements or overrides an inherited method and must carry #[\\Override].")
            ->identifier('innis.overrideAttribute')
            ->line($method->getStartLine())
            ->build();
    }

    private function overridesContract(ClassReflection $class, string $method, bool $firstPartyOnly): bool
    {
        foreach ([...$class->getParents(), ...$class->getInterfaces()] as $ancestor) {
            if (!$ancestor->hasNativeMethod($method)) {
                continue;
            }
            if (!$firstPartyOnly || $this->isFirstParty($ancestor->getNativeMethod($method)->getDeclaringClass())) {
                return true;
            }
        }

        return false;
    }

    /**
     * First-party: the contract is declared in this codebase, not pulled from a dependency or a
     * PHP built-in. Keyed on the declaring class's file so it is independent of the root namespace.
     */
    private function isFirstParty(ClassReflection $declaring): bool
    {
        $file = $declaring->getFileName();

        return null !== $file && !str_contains($file, '/vendor/');
    }
}
