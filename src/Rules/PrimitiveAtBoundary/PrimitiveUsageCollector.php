<?php

declare(strict_types=1);

namespace Innis\CodingStandards\Rules\PrimitiveAtBoundary;

use Innis\CodingStandards\Support\ClassNames;
use Innis\CodingStandards\Support\DeliberateFence;
use Innis\CodingStandards\Support\Layer;
use Override;
use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Collectors\Collector;
use PHPStan\Node\InClassNode;

/**
 * Collects every `string`/`int` parameter and property in a Domain or Application class — the raw
 * primitives that might be threading past a boundary where a value object should be. The rule keeps
 * only those whose name matches an existing value object.
 *
 * @implements Collector<InClassNode, list<array{key: string, name: string, line: int, where: string, enclosing: string}>>
 */
final class PrimitiveUsageCollector implements Collector
{
    public function __construct(private readonly DeliberateFence $fence)
    {
    }

    #[Override]
    public function getNodeType(): string
    {
        return InClassNode::class;
    }

    /**
     * @return list<array{key: string, name: string, line: int, where: string, enclosing: string}>|null
     */
    #[Override]
    public function processNode(Node $node, Scope $scope): ?array
    {
        $original = $node->getOriginalNode();
        if (!$original instanceof Class_) {
            return null;
        }

        $fqcn = $node->getClassReflection()->getName();
        $namespace = ClassNames::namespace($fqcn);
        $layer = Layer::of($namespace);
        if (ClassNames::isTestNamespace($namespace) || (Layer::DOMAIN !== $layer && Layer::APPLICATION !== $layer)) {
            return null;
        }

        $enclosing = ClassNames::conceptKey(ClassNames::short($fqcn));
        $records = [
            ...$this->parameterRecords($original, $enclosing),
            ...$this->propertyRecords($original, $enclosing),
        ];

        return [] === $records ? null : $records;
    }

    /**
     * @return list<array{key: string, name: string, line: int, where: string, enclosing: string}>
     */
    private function parameterRecords(Class_ $class, string $enclosing): array
    {
        $records = [];
        foreach ($class->getMethods() as $method) {
            if (!$method->isPublic() || $this->fence->isFenced($method, $class)) {
                continue;
            }
            foreach ($method->params as $param) {
                if (!$this->isScalarPrimitive($param->type) || !$param->var instanceof Variable || !is_string($param->var->name)) {
                    continue;
                }
                $name = $param->var->name;
                $records[] = [
                    'key' => ClassNames::conceptKey($name),
                    'name' => $name,
                    'line' => $param->var->getStartLine(),
                    'where' => "parameter \${$name} of {$method->name->toString()}()",
                    'enclosing' => $enclosing,
                ];
            }
        }

        return $records;
    }

    /**
     * @return list<array{key: string, name: string, line: int, where: string, enclosing: string}>
     */
    private function propertyRecords(Class_ $class, string $enclosing): array
    {
        $records = [];
        foreach ($class->getProperties() as $property) {
            if (!$this->isScalarPrimitive($property->type) || $this->fence->isFenced($property, $class)) {
                continue;
            }
            foreach ($property->props as $item) {
                $records[] = [
                    'key' => ClassNames::conceptKey($item->name->toString()),
                    'name' => $item->name->toString(),
                    'line' => $item->getStartLine(),
                    'where' => "property \${$item->name->toString()}",
                    'enclosing' => $enclosing,
                ];
            }
        }

        return $records;
    }

    private function isScalarPrimitive(?Node $type): bool
    {
        if ($type instanceof NullableType) {
            $type = $type->type;
        }

        return $type instanceof Identifier && in_array($type->toLowerString(), ['string', 'int'], true);
    }
}
