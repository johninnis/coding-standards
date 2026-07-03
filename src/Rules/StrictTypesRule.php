<?php

declare(strict_types=1);

namespace Innis\CodingStandards\Rules;

use Override;
use PhpParser\Node;
use PhpParser\Node\Scalar\Int_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Declare_;
use PhpParser\Node\Stmt\Namespace_;
use PHPStan\Analyser\Scope;
use PHPStan\Node\FileNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Every file that declares a type must open with declare(strict_types=1).
 *
 * @implements Rule<FileNode>
 */
final class StrictTypesRule implements Rule
{
    #[Override]
    public function getNodeType(): string
    {
        return FileNode::class;
    }

    #[Override]
    public function processNode(Node $node, Scope $scope): array
    {
        $statements = $node->getNodes();

        if (!$this->declaresType($statements) || $this->hasStrictTypes($statements)) {
            return [];
        }

        return [
            RuleErrorBuilder::message('Missing declare(strict_types=1).')
                ->identifier('innis.strictTypes')
                ->line(1)
                ->build(),
        ];
    }

    /** @param array<Node> $statements */
    private function declaresType(array $statements): bool
    {
        foreach ($statements as $statement) {
            if ($statement instanceof ClassLike) {
                return true;
            }
            if ($statement instanceof Namespace_ && $this->declaresType($statement->stmts)) {
                return true;
            }
        }

        return false;
    }

    /** @param array<Node> $statements */
    private function hasStrictTypes(array $statements): bool
    {
        foreach ($statements as $statement) {
            if (!$statement instanceof Declare_) {
                continue;
            }
            foreach ($statement->declares as $declare) {
                if ('strict_types' === $declare->key->toString()
                    && $declare->value instanceof Int_
                    && 1 === $declare->value->value
                ) {
                    return true;
                }
            }
        }

        return false;
    }
}
