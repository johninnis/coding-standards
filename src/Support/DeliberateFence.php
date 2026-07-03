<?php

declare(strict_types=1);

namespace Innis\CodingStandards\Support;

use PhpParser\Node;

/**
 * The ecosystem's Chesterton's-Fence convention: a `// Deliberate: …` comment or an `ADR-NNNN`
 * reference pinned at the code marks a documented, sanctioned departure.
 *
 * The marker is read from a node's own attached comments (php-parser attaches leading line- and
 * doc-comments to the node they precede), so a rule checks the fence on the exact node it reports
 * on — a method, a class, a property. Passing more than one node treats the fence as present when
 * any of them carries it, which lets a rule accept a marker on the reported node *or* on an
 * enclosing one (a class fence covering a member).
 */
final class DeliberateFence
{
    public function isFenced(Node ...$nodes): bool
    {
        foreach ($nodes as $node) {
            foreach ($node->getComments() as $comment) {
                if ($this->isMarker($comment->getText())) {
                    return true;
                }
            }
        }

        return false;
    }

    private function isMarker(string $text): bool
    {
        return 1 === preg_match('~//\s*Deliberate:~', $text) || 1 === preg_match('~ADR-\d{4}~', $text);
    }
}
