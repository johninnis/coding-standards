<?php

declare(strict_types=1);

namespace Innis\CodingStandards\Rules\PortPlacement;

use Innis\CodingStandards\Support\ClassNames;
use Innis\CodingStandards\Support\Layer;
use Override;
use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Collectors\Collector;

/**
 * Collects the short name of every class the package's production code constructs itself
 * (`new X(...)`).
 *
 * Only constructions inside a production layer namespace (Domain/Application/Infrastructure/
 * Presentation) count as the package "owning" the collaborator. A concrete implementation that is
 * only `new`ed by a host wiring it up — the example/demo scripts, which live in the global
 * namespace — is a driven port, not an internal collaborator, and must not trip `portPlacement`.
 * Test namespaces are excluded for the same reason.
 *
 * @implements Collector<New_, string>
 */
final class InstantiationCollector implements Collector
{
    #[Override]
    public function getNodeType(): string
    {
        return New_::class;
    }

    #[Override]
    public function processNode(Node $node, Scope $scope): ?string
    {
        $namespace = $scope->getNamespace() ?? '';
        if (null === Layer::of($namespace) || ClassNames::isTestNamespace($namespace)) {
            return null;
        }

        return $node->class instanceof Name ? ClassNames::short($node->class->toString()) : null;
    }
}
