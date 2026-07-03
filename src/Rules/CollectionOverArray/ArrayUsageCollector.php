<?php

declare(strict_types=1);

namespace Innis\CodingStandards\Rules\CollectionOverArray;

use Innis\CodingStandards\Support\ClassNames;
use Innis\CodingStandards\Support\TypedCollections;
use Override;
use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Collectors\Collector;
use PHPStan\Node\InClassNode;
use PHPStan\Reflection\ParametersAcceptor;
use PHPStan\Type\Type;

/**
 * Collects every public method signature (parameter or return) whose type is an array of a
 * known object element — the raw form the rule replaces with a typed collection. The typed
 * collections themselves are skipped: their constructors take an array at the sanctioned
 * array-to-collection edge.
 *
 * @implements Collector<InClassNode, list<array{element: string, line: int, where: string}>>
 */
final class ArrayUsageCollector implements Collector
{
    #[Override]
    public function getNodeType(): string
    {
        return InClassNode::class;
    }

    /**
     * @return list<array{element: string, line: int, where: string}>|null
     */
    #[Override]
    public function processNode(Node $node, Scope $scope): ?array
    {
        $original = $node->getOriginalNode();
        if (!$original instanceof Class_) {
            return null;
        }

        $reflection = $node->getClassReflection();
        $shortName = ClassNames::short($reflection->getName());
        if (ClassNames::isTestNamespace(ClassNames::namespace($reflection->getName())) || $this->isTypedCollection($shortName, $original)) {
            return null;
        }

        $records = [];
        foreach ($original->getMethods() as $method) {
            if (!$method->isPublic() || !$reflection->hasNativeMethod($method->name->toString())) {
                continue;
            }
            foreach ($this->methodRecords($method, $reflection->getNativeMethod($method->name->toString())->getVariants()[0]) as $record) {
                $records[] = $record;
            }
        }

        return [] === $records ? null : $records;
    }

    private function isTypedCollection(string $shortName, Class_ $class): bool
    {
        return str_ends_with($shortName, 'Collection') || TypedCollections::extendsBase($class);
    }

    /**
     * @return list<array{element: string, line: int, where: string}>
     */
    private function methodRecords(ClassMethod $method, ParametersAcceptor $variant): array
    {
        $methodName = $method->name->toString();
        $parameterLines = $this->parameterLines($method);

        $records = [];
        foreach ($this->elementShortNames($variant->getReturnType()) as $element) {
            $records[] = ['element' => $element, 'line' => $method->getStartLine(), 'where' => "the return of {$methodName}()"];
        }
        foreach ($variant->getParameters() as $parameter) {
            $line = $parameterLines[$parameter->getName()] ?? $method->getStartLine();
            foreach ($this->elementShortNames($parameter->getType()) as $element) {
                $records[] = ['element' => $element, 'line' => $line, 'where' => "parameter \${$parameter->getName()} of {$methodName}()"];
            }
        }

        return $records;
    }

    /**
     * @return array<string, int>
     */
    private function parameterLines(ClassMethod $method): array
    {
        $lines = [];
        foreach ($method->params as $param) {
            if ($param->var instanceof Variable && is_string($param->var->name)) {
                $lines[$param->var->name] = $param->getStartLine();
            }
        }

        return $lines;
    }

    /**
     * @return list<string>
     */
    private function elementShortNames(Type $type): array
    {
        if (!$type->isArray()->yes()) {
            return [];
        }

        return array_map(ClassNames::short(...), $type->getIterableValueType()->getObjectClassNames());
    }
}
