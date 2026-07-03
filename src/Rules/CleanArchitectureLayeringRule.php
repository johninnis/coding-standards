<?php

declare(strict_types=1);

namespace Innis\CodingStandards\Rules;

use Innis\CodingStandards\Support\ClassNames;
use Innis\CodingStandards\Support\Layer;
use Override;
use PhpParser\Node;
use PhpParser\Node\Stmt\GroupUse;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\UseItem;
use PHPStan\Analyser\Scope;
use PHPStan\Node\FileNode;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Dependencies must point inward: Domain imports only Domain; Application imports
 * Application/Domain; Infrastructure imports Infrastructure/Application/Domain;
 * Presentation may import any layer. The layer of a file and of each `use` import is
 * the first Domain/Application/Infrastructure/Presentation segment of its namespace.
 *
 * @implements Rule<FileNode>
 */
final class CleanArchitectureLayeringRule implements Rule
{
    #[Override]
    public function getNodeType(): string
    {
        return FileNode::class;
    }

    #[Override]
    public function processNode(Node $node, Scope $scope): array
    {
        $errors = [];
        foreach ($node->getNodes() as $statement) {
            if ($statement instanceof Namespace_) {
                $namespace = $statement->name?->toString() ?? '';
                foreach ($this->violations($namespace, $statement->stmts) as $error) {
                    $errors[] = $error;
                }
            }
        }

        return $errors;
    }

    /**
     * @param array<Node> $statements
     *
     * @return list<IdentifierRuleError>
     */
    private function violations(string $namespace, array $statements): array
    {
        $fileLayer = Layer::of($namespace);
        if (null === $fileLayer || ClassNames::isTestNamespace($namespace)) {
            return [];
        }

        $errors = [];
        foreach ($statements as $statement) {
            foreach ($this->normalImports($statement) as $import) {
                $error = $this->check($fileLayer, $import, $statement->getStartLine());
                if (null !== $error) {
                    $errors[] = $error;
                }
            }
        }

        return $errors;
    }

    private function check(string $fileLayer, string $import, int $line): ?IdentifierRuleError
    {
        $importLayer = Layer::of($import);
        if (null === $importLayer || Layer::allows($fileLayer, $importLayer)) {
            return null;
        }

        return RuleErrorBuilder::message("{$fileLayer} imports {$importLayer} ({$import}); dependencies must point inward.")
            ->identifier('innis.cleanArchitecture')
            ->line($line)
            ->build();
    }

    /**
     * Fully-qualified names imported by a `use` statement, class imports only
     * (function and const imports are ignored).
     *
     * @return list<string>
     */
    private function normalImports(Node $statement): array
    {
        if ($statement instanceof Use_ && Use_::TYPE_NORMAL === $statement->type) {
            return $this->itemNames('', $statement->uses);
        }
        if ($statement instanceof GroupUse && Use_::TYPE_NORMAL === $statement->type) {
            return $this->itemNames($statement->prefix->toString(), $statement->uses);
        }

        return [];
    }

    /**
     * @param array<UseItem> $uses
     *
     * @return list<string>
     */
    private function itemNames(string $prefix, array $uses): array
    {
        $names = [];
        foreach ($uses as $use) {
            if (Use_::TYPE_UNKNOWN !== $use->type && Use_::TYPE_NORMAL !== $use->type) {
                continue;
            }
            $name = $use->name->toString();
            $names[] = '' === $prefix ? $name : $prefix.'\\'.$name;
        }

        return $names;
    }
}
