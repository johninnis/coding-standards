<?php

declare(strict_types=1);

namespace Innis\CodingStandards\Rules;

use Innis\CodingStandards\Support\Attributes;
use Innis\CodingStandards\Support\ClassNames;
use Innis\CodingStandards\Support\DeliberateFence;
use Override;
use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\EnumCase;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Property;
use PhpParser\NodeFinder;
use PHPStan\Analyser\Scope;
use PHPStan\Node\FileNode;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Declared identifiers use UK English. Only the names we declare — types, methods,
 * properties, parameters, constants, enum cases — are checked; string literals are left
 * alone, so a wire-format value that keeps its spec spelling is never touched.
 *
 * @implements Rule<FileNode>
 */
final class UkEnglishRule implements Rule
{
    /**
     * US spelling => UK spelling, matched as a case-insensitive substring of an identifier.
     * The set is curated to avoid homographs: no `-er`/`-re` stems (which would fire inside
     * `parameter`, `diameter`), and no stem that is a prefix of its own UK form (which would
     * fire on the already-correct word). See docs/adr/0006.
     *
     * @var array<string, string>
     */
    private const array US_TO_UK = [
        'ization' => 'isation',
        'yzing' => 'ysing',
        'yze' => 'yse',
        'serialize' => 'serialise',
        'normalize' => 'normalise',
        'organize' => 'organise',
        'authorize' => 'authorise',
        'initialize' => 'initialise',
        'finalize' => 'finalise',
        'capitalize' => 'capitalise',
        'synchronize' => 'synchronise',
        'prioritize' => 'prioritise',
        'categorize' => 'categorise',
        'customize' => 'customise',
        'optimize' => 'optimise',
        'recognize' => 'recognise',
        'summarize' => 'summarise',
        'minimize' => 'minimise',
        'maximize' => 'maximise',
        'utilize' => 'utilise',
        'sanitize' => 'sanitise',
        'standardize' => 'standardise',
        'tokenize' => 'tokenise',
        'memoize' => 'memoise',
        'emphasize' => 'emphasise',
        'behavior' => 'behaviour',
        'color' => 'colour',
        'favor' => 'favour',
        'flavor' => 'flavour',
        'honor' => 'honour',
        'neighbor' => 'neighbour',
        'defense' => 'defence',
        'offense' => 'offence',
        'pretense' => 'pretence',
        'canceled' => 'cancelled',
        'canceling' => 'cancelling',
        'labeled' => 'labelled',
        'labeling' => 'labelling',
        'modeling' => 'modelling',
        'signaling' => 'signalling',
        'traveling' => 'travelling',
        'fulfill' => 'fulfil',
    ];

    public function __construct(private readonly DeliberateFence $fence)
    {
    }

    #[Override]
    public function getNodeType(): string
    {
        return FileNode::class;
    }

    #[Override]
    public function processNode(Node $node, Scope $scope): array
    {
        $nodes = $node->getNodes();
        if ($this->isTestFile($nodes)) {
            return [];
        }

        $errors = [];
        foreach ($this->declaredNames($nodes) as [$name, $line]) {
            $error = $this->spellingError($name, $line);
            if (null !== $error) {
                $errors[] = $error;
            }
        }

        return $errors;
    }

    /**
     * @param array<Node> $nodes
     */
    private function isTestFile(array $nodes): bool
    {
        foreach (new NodeFinder()->findInstanceOf($nodes, Namespace_::class) as $namespace) {
            if (ClassNames::isTestNamespace($namespace->name?->toString() ?? '')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Walked hierarchically rather than by a flat node search, because a fence attaches to the
     * enclosing node: a parameter's marker sits on its method, a constant's on the `const`
     * statement, and a class-level marker covers the class and its members. (Parameters of a
     * closure nested in a body are not checked — declared API identifiers are.).
     *
     * @param array<Node> $statements
     *
     * @return list<array{0: string, 1: int}>
     */
    private function declaredNames(array $statements): array
    {
        $names = [];
        foreach ($statements as $statement) {
            if ($statement instanceof Namespace_) {
                $names = [...$names, ...$this->declaredNames($statement->stmts)];
            } elseif ($statement instanceof ClassLike) {
                $names = [...$names, ...$this->classNames($statement)];
            } elseif ($statement instanceof Function_) {
                $names = [...$names, ...$this->functionNames($statement)];
            }
        }

        return $names;
    }

    /**
     * @return list<array{0: string, 1: int}>
     */
    private function classNames(ClassLike $class): array
    {
        if ($this->fence->isFenced($class)) {
            return [];
        }

        $names = [];
        if (null !== $class->name) {
            $names[] = [$class->name->toString(), $class->name->getStartLine()];
        }
        foreach ($class->stmts as $member) {
            $names = [...$names, ...$this->memberNames($member)];
        }

        return $names;
    }

    /**
     * @return list<array{0: string, 1: int}>
     */
    private function memberNames(Node $member): array
    {
        if ($this->fence->isFenced($member)) {
            return [];
        }
        if ($member instanceof ClassMethod) {
            // An overriding method's name comes from the contract, not ours to respell; see ADR-0006.
            $names = Attributes::isPresent($member, 'Override')
                ? []
                : [[$member->name->toString(), $member->name->getStartLine()]];

            return [...$names, ...$this->parameterNames($member->params)];
        }

        $names = [];
        if ($member instanceof Property) {
            foreach ($member->props as $item) {
                $names[] = [$item->name->toString(), $item->name->getStartLine()];
            }
        } elseif ($member instanceof ClassConst) {
            foreach ($member->consts as $const) {
                $names[] = [$const->name->toString(), $const->name->getStartLine()];
            }
        } elseif ($member instanceof EnumCase) {
            $names[] = [$member->name->toString(), $member->name->getStartLine()];
        }

        return $names;
    }

    /**
     * @return list<array{0: string, 1: int}>
     */
    private function functionNames(Function_ $function): array
    {
        if ($this->fence->isFenced($function)) {
            return [];
        }

        return [
            [$function->name->toString(), $function->name->getStartLine()],
            ...$this->parameterNames($function->params),
        ];
    }

    /**
     * @param array<Param> $params
     *
     * @return list<array{0: string, 1: int}>
     */
    private function parameterNames(array $params): array
    {
        $names = [];
        foreach ($params as $param) {
            if ($param->var instanceof Variable && is_string($param->var->name)) {
                $names[] = [$param->var->name, $param->var->getStartLine()];
            }
        }

        return $names;
    }

    private function spellingError(string $name, int $line): ?IdentifierRuleError
    {
        foreach (self::US_TO_UK as $us => $uk) {
            $position = stripos($name, $us);
            if (false === $position) {
                continue;
            }

            $matched = substr($name, $position, strlen($us));
            $corrected = substr($name, 0, $position).$this->matchCase($matched, $uk).substr($name, $position + strlen($us));

            return RuleErrorBuilder::message("Identifier {$name} uses a US spelling; prefer {$corrected} (UK English).")
                ->identifier('innis.ukEnglish')
                ->line($line)
                ->build();
        }

        return null;
    }

    private function matchCase(string $matched, string $replacement): string
    {
        if ($matched === strtoupper($matched)) {
            return strtoupper($replacement);
        }
        if ($matched === ucfirst(strtolower($matched))) {
            return ucfirst($replacement);
        }

        return $replacement;
    }
}
