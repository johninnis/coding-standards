<?php

declare(strict_types=1);

namespace Innis\CodingStandards\Rules;

use Innis\CodingStandards\Support\ClassNames;
use Override;
use PhpParser\Node;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\NodeFinder;
use PHPStan\Analyser\Scope;
use PHPStan\Node\FileNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * No emojis anywhere — not in code, comments, or output. The whole source is scanned, since
 * an emoji can sit in a string, a comment, or a docblock the parse tree would drop.
 *
 * @implements Rule<FileNode>
 */
final class NoEmojisRule implements Rule
{
    private const string EMOJI_PATTERN = '/[\x{1F000}-\x{1FAFF}\x{1F1E6}-\x{1F1FF}\x{2600}-\x{27BF}\x{2B00}-\x{2BFF}\x{FE00}-\x{FE0F}]/u';

    #[Override]
    public function getNodeType(): string
    {
        return FileNode::class;
    }

    #[Override]
    public function processNode(Node $node, Scope $scope): array
    {
        if ($this->isTestFile($node->getNodes())) {
            return [];
        }

        $source = @file_get_contents($scope->getFile());
        if (false === $source) {
            return [];
        }

        $errors = [];
        foreach (explode("\n", $source) as $index => $line) {
            if (1 === preg_match(self::EMOJI_PATTERN, $line)) {
                $errors[] = RuleErrorBuilder::message('Emoji found; no emojis are permitted anywhere (code, comments, or output).')
                    ->identifier('innis.noEmojis')
                    ->line($index + 1)
                    ->build();
            }
        }

        return $errors;
    }

    /**
     * Test fixtures legitimately contain unicode and emoji to exercise UTF-8 handling — a
     * round-trip assertion, a rejection test. The ban is on emoji as decoration in shipped code,
     * comments, and output, not on emoji as deliberate test input.
     *
     * @param array<Node> $nodes
     */
    private function isTestFile(array $nodes): bool
    {
        foreach ((new NodeFinder())->findInstanceOf($nodes, Namespace_::class) as $namespace) {
            if (ClassNames::isTestNamespace($namespace->name?->toString() ?? '')) {
                return true;
            }
        }

        return false;
    }
}
