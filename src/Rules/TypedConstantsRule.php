<?php

declare(strict_types=1);

namespace Innis\CodingStandards\Rules;

use Innis\CodingStandards\Support\ClassNames;
use Override;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassNode;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Class, interface and enum constants declare a type (PHP 8.3). A typed constant lets the
 * analyser carry the value's type instead of inferring it.
 *
 * @implements Rule<InClassNode>
 */
final class TypedConstantsRule implements Rule
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
        if (ClassNames::isTestNamespace(ClassNames::namespace($node->getClassReflection()->getName()))) {
            return [];
        }

        $errors = [];
        foreach ($classLike->getConstants() as $constant) {
            if (null !== $constant->type) {
                continue;
            }
            foreach ($constant->consts as $const) {
                $errors[] = $this->error($const->name->toString(), $const->getStartLine());
            }
        }

        return $errors;
    }

    private function error(string $name, int $line): IdentifierRuleError
    {
        return RuleErrorBuilder::message("Constant {$name} must declare a type.")
            ->identifier('innis.typedConstants')
            ->line($line)
            ->build();
    }
}
