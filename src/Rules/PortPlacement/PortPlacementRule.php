<?php

declare(strict_types=1);

namespace Innis\CodingStandards\Rules\PortPlacement;

use Override;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\CollectedDataNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Whole-package check: an `Application/Port/` interface whose implementation is an
 * `Application/Service/` class the package constructs itself (`new X(...)`) is a mis-filed
 * internal collaborator — its interface belongs in `Application/Service/`, not `Port/`.
 *
 * The "constructed by the package" guard keeps a host-supplied driven port (one the host
 * wires and can replace, never `new`ed by the package) out of the results. An ADR fence on
 * the interface also exempts it (handled by the collector).
 *
 * @implements Rule<CollectedDataNode>
 */
final class PortPlacementRule implements Rule
{
    #[Override]
    public function getNodeType(): string
    {
        return CollectedDataNode::class;
    }

    #[Override]
    public function processNode(Node $node, Scope $scope): array
    {
        $constructed = $this->constructedShortNames($node);
        $implementorsByInterface = $this->implementorsByInterface($node);

        $errors = [];
        foreach ($node->get(PortInterfaceCollector::class) as $file => $ports) {
            foreach ($ports as $port) {
                foreach ($implementorsByInterface[$port['name']] ?? [] as $implementor) {
                    if (!isset($constructed[$implementor])) {
                        continue;
                    }
                    $errors[] = RuleErrorBuilder::message("{$port['name']} is in Application/Port, but its implementation {$implementor} is an Application/Service class the package constructs itself; an internal collaborator's interface belongs in Application/Service, not Port.")
                        ->identifier('innis.portPlacement')
                        ->file($file)
                        ->line($port['line'])
                        ->build();
                    break;
                }
            }
        }

        return $errors;
    }

    /**
     * @return array<string, true>
     */
    private function constructedShortNames(CollectedDataNode $node): array
    {
        $constructed = [];
        foreach ($node->get(InstantiationCollector::class) as $names) {
            foreach ($names as $name) {
                $constructed[$name] = true;
            }
        }

        return $constructed;
    }

    /**
     * Interface short name => list of implementing Application/Service class short names.
     *
     * @return array<string, list<string>>
     */
    private function implementorsByInterface(CollectedDataNode $node): array
    {
        $map = [];
        foreach ($node->get(ServiceImplementorCollector::class) as $services) {
            foreach ($services as $service) {
                foreach ($service['interfaces'] as $interface) {
                    $map[$interface][] = $service['name'];
                }
            }
        }

        return $map;
    }
}
