<?php

declare(strict_types=1);

namespace Innis\CodingStandards\Rules;

use Innis\CodingStandards\Support\ClassNames;
use Innis\CodingStandards\Support\DeliberateFence;
use Innis\CodingStandards\Support\Layer;
use Override;
use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\NodeFinder;
use PHPStan\Analyser\Scope;
use PHPStan\Node\FileNode;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Domain logic is pure: I/O, time, randomness and process/environment access live behind
 * ports in Infrastructure, never woven through the Domain layer. This flags the built-in
 * calls, zero-argument clock construction, and request superglobals that break that purity
 * inside a `Domain/` namespace. An ADR-fenced departure is exempt.
 *
 * @implements Rule<FileNode>
 */
final class DomainPurityRule implements Rule
{
    /** @var array<string, string> lowercased built-in function name => what it does */
    private const array IMPURE_FUNCTIONS = [
        'time' => 'reads the clock', 'microtime' => 'reads the clock', 'hrtime' => 'reads the clock',
        'date' => 'reads the clock', 'gmdate' => 'reads the clock', 'mktime' => 'reads the clock',
        'gmmktime' => 'reads the clock', 'strtotime' => 'reads the clock', 'getdate' => 'reads the clock',
        'localtime' => 'reads the clock',
        'rand' => 'uses randomness', 'mt_rand' => 'uses randomness', 'random_int' => 'uses randomness',
        'random_bytes' => 'uses randomness', 'uniqid' => 'uses randomness', 'lcg_value' => 'uses randomness',
        'array_rand' => 'uses randomness', 'str_shuffle' => 'uses randomness', 'shuffle' => 'uses randomness',
        'fopen' => 'performs I/O', 'fread' => 'performs I/O', 'fwrite' => 'performs I/O',
        'fgets' => 'performs I/O', 'fclose' => 'performs I/O', 'file_get_contents' => 'performs I/O',
        'file_put_contents' => 'performs I/O', 'file_exists' => 'performs I/O', 'is_file' => 'performs I/O',
        'is_dir' => 'performs I/O', 'unlink' => 'performs I/O', 'mkdir' => 'performs I/O',
        'rmdir' => 'performs I/O', 'rename' => 'performs I/O', 'copy' => 'performs I/O',
        'scandir' => 'performs I/O', 'glob' => 'performs I/O', 'readfile' => 'performs I/O',
        'tmpfile' => 'performs I/O', 'touch' => 'performs I/O',
        'getenv' => 'reads the environment', 'putenv' => 'reads the environment',
        'ini_get' => 'reads the environment', 'ini_set' => 'reads the environment',
        'php_sapi_name' => 'reads the environment', 'gethostname' => 'reads the environment',
        'print_r' => 'writes output', 'printf' => 'writes output', 'vprintf' => 'writes output',
        'var_dump' => 'writes output', 'error_log' => 'writes output', 'header' => 'writes output',
        'http_response_code' => 'writes output', 'setcookie' => 'writes output', 'flush' => 'writes output',
        'sleep' => 'pauses execution', 'usleep' => 'pauses execution', 'time_nanosleep' => 'pauses execution',
        'time_sleep_until' => 'pauses execution',
        'exec' => 'executes a process', 'shell_exec' => 'executes a process', 'system' => 'executes a process',
        'passthru' => 'executes a process', 'proc_open' => 'executes a process', 'popen' => 'executes a process',
        'fsockopen' => 'accesses the network', 'stream_socket_client' => 'accesses the network',
        'curl_exec' => 'accesses the network', 'mail' => 'accesses the network',
    ];

    private const array CLOCK_CLASSES = ['DateTime', 'DateTimeImmutable'];

    private const array SUPERGLOBALS = [
        '_GET' => 'reads the request', '_POST' => 'reads the request', '_REQUEST' => 'reads the request',
        '_SERVER' => 'reads the request', '_ENV' => 'reads the environment', '_COOKIE' => 'reads the request',
        '_FILES' => 'reads the request', '_SESSION' => 'reads session state', 'GLOBALS' => 'reads global state',
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
        $errors = [];
        foreach ($node->getNodes() as $statement) {
            if (!$statement instanceof Namespace_) {
                continue;
            }
            $namespace = $statement->name?->toString() ?? '';
            if (Layer::DOMAIN !== Layer::of($namespace) || ClassNames::isTestNamespace($namespace)) {
                continue;
            }
            foreach ($this->violations($statement->stmts) as $error) {
                $errors[] = $error;
            }
        }

        return $errors;
    }

    /**
     * @param array<Node> $statements
     *
     * @return list<IdentifierRuleError>
     */
    private function violations(array $statements): array
    {
        $errors = [];

        // The fence lands on the enclosing member or class, not the impure call itself; see ADR-0011.
        foreach ($statements as $statement) {
            if ($statement instanceof ClassLike) {
                if ($this->fence->isFenced($statement)) {
                    continue;
                }
                foreach ($statement->stmts as $member) {
                    if (!$this->fence->isFenced($member)) {
                        array_push($errors, ...$this->scan($member));
                    }
                }

                continue;
            }
            if (!$this->fence->isFenced($statement)) {
                array_push($errors, ...$this->scan($statement));
            }
        }

        return $errors;
    }

    /**
     * @return list<IdentifierRuleError>
     */
    private function scan(Node $node): array
    {
        $finder = new NodeFinder();
        $errors = [];

        foreach ($finder->findInstanceOf([$node], FuncCall::class) as $call) {
            $error = $this->functionCallError($call);
            if (null !== $error) {
                $errors[] = $error;
            }
        }

        foreach ($finder->findInstanceOf([$node], New_::class) as $new) {
            $error = $this->clockConstructionError($new);
            if (null !== $error) {
                $errors[] = $error;
            }
        }

        foreach ($finder->findInstanceOf([$node], Variable::class) as $variable) {
            $error = $this->superglobalError($variable);
            if (null !== $error) {
                $errors[] = $error;
            }
        }

        return $errors;
    }

    private function functionCallError(FuncCall $call): ?IdentifierRuleError
    {
        if (!$call->name instanceof Node\Name) {
            return null;
        }
        $function = strtolower(ClassNames::short($call->name->toString()));
        $activity = self::IMPURE_FUNCTIONS[$function] ?? null;
        if (null === $activity) {
            return null;
        }

        return $this->error("Domain must stay pure: {$function}() {$activity}; move it behind a port in Infrastructure.", $call->getStartLine());
    }

    private function clockConstructionError(New_ $new): ?IdentifierRuleError
    {
        if (!$new->class instanceof Node\Name
            || !in_array(ClassNames::short($new->class->toString()), self::CLOCK_CLASSES, true)
            || [] !== $new->getArgs()
        ) {
            return null;
        }

        return $this->error('Domain must stay pure: constructing '.ClassNames::short($new->class->toString()).'() with no argument reads the clock; inject the time behind a port.', $new->getStartLine());
    }

    private function superglobalError(Variable $variable): ?IdentifierRuleError
    {
        if (!is_string($variable->name)) {
            return null;
        }
        $activity = self::SUPERGLOBALS[$variable->name] ?? null;
        if (null === $activity) {
            return null;
        }

        return $this->error("Domain must stay pure: \${$variable->name} {$activity}; pass the value in rather than reading a superglobal.", $variable->getStartLine());
    }

    private function error(string $message, int $line): IdentifierRuleError
    {
        return RuleErrorBuilder::message($message)
            ->identifier('innis.domainPurity')
            ->line($line)
            ->build();
    }
}
