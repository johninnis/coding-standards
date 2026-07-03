<?php

declare(strict_types=1);

namespace Innis\CodingStandards\Rules;

use Innis\CodingStandards\Support\ClassNames;
use Override;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use Throwable;

/**
 * A fault (throwable, or `*Exception`) lives in an `Exception/` namespace and is either
 * `final` (a leaf) or `abstract` (a base) — never an extensible concrete class.
 *
 * @implements Rule<InClassNode>
 */
final class ExceptionShapeRule implements Rule
{
    #[Override]
    public function getNodeType(): string
    {
        return InClassNode::class;
    }

    #[Override]
    public function processNode(Node $node, Scope $scope): array
    {
        $original = $node->getOriginalNode();
        if (!$original instanceof Class_) {
            return [];
        }

        $reflection = $node->getClassReflection();
        $namespace = ClassNames::namespace($reflection->getName());
        if (ClassNames::isTestNamespace($namespace)) {
            return [];
        }

        $name = ClassNames::short($reflection->getName());
        if (!$reflection->isSubclassOf(Throwable::class) && !str_ends_with($name, 'Exception')) {
            return [];
        }

        $errors = [];
        if (!ClassNames::hasSegment($namespace, 'Exception')) {
            $errors[] = RuleErrorBuilder::message("Fault {$name} must live in an Exception/ namespace.")
                ->identifier('innis.exceptionPlacement')
                ->line($original->getStartLine())
                ->build();
        }

        if (!$original->isFinal() && !$original->isAbstract()) {
            $errors[] = RuleErrorBuilder::message("Exception {$name} must be 'final' (leaf) or 'abstract' (base).")
                ->identifier('innis.exceptionShape')
                ->line($original->getStartLine())
                ->build();
        }

        return $errors;
    }
}
