<?php

declare(strict_types=1);

namespace Innis\CodingStandards\Tests\Rules;

use Innis\CodingStandards\Rules\PortPlacement\InstantiationCollector;
use Innis\CodingStandards\Rules\PortPlacement\PortInterfaceCollector;
use Innis\CodingStandards\Rules\PortPlacement\PortPlacementRule;
use Innis\CodingStandards\Rules\PortPlacement\ServiceImplementorCollector;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<PortPlacementRule>
 */
final class PortPlacementRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new PortPlacementRule();
    }

    protected function getCollectors(): array
    {
        return [
            new InstantiationCollector(),
            new ServiceImplementorCollector(),
            new PortInterfaceCollector(),
        ];
    }

    public function testFlagsSelfConstructedServiceBehindPort(): void
    {
        $this->analyse([
            __DIR__.'/../data/Port/GreeterInterface.php',
            __DIR__.'/../data/Port/Greeter.php',
            __DIR__.'/../data/Port/Consumer.php',
        ], [
            [
                "GreeterInterface is in Application/Port, but its implementation Greeter is an Application/Service class the package constructs itself; an internal collaborator's interface belongs in Application/Service, not Port.",
                7,
            ],
        ]);
    }

    public function testAcceptsPortNeverConstructedByPackage(): void
    {
        $this->analyse([
            __DIR__.'/../data/PortClean/LoggerInterface.php',
            __DIR__.'/../data/PortClean/FileLogger.php',
        ], []);
    }

    public function testStillFlagsAFencedPort(): void
    {
        // The rule takes no fence (ADR-0015): the sanctioned way to declare a driven port is to move
        // the `new` out to host wiring, which is the same act that makes it one.
        $this->analyse([
            __DIR__.'/../data/PortFenced/FencedGreeterInterface.php',
            __DIR__.'/../data/PortFenced/FencedGreeter.php',
            __DIR__.'/../data/PortFenced/FencedConsumer.php',
        ], [
            [
                "FencedGreeterInterface is in Application/Port, but its implementation FencedGreeter is an Application/Service class the package constructs itself; an internal collaborator's interface belongs in Application/Service, not Port.",
                8,
            ],
        ]);
    }

    public function testAcceptsPortConstructedOnlyByHostWiring(): void
    {
        // The concrete implementation is `new`ed only by a global-namespace host script,
        // not by the package's own layered code — a driven port, not an internal collaborator.
        $this->analyse([
            __DIR__.'/../data/Port/GreeterInterface.php',
            __DIR__.'/../data/Port/Greeter.php',
            __DIR__.'/../data/Port/HostWiring.php',
        ], []);
    }
}
