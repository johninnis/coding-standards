<?php

declare(strict_types=1);

namespace Innis\CodingStandards\Tests\Rules;

use Innis\CodingStandards\Rules\PortPlacement\InstantiationCollector;
use Innis\CodingStandards\Rules\PortPlacement\PortInterfaceCollector;
use Innis\CodingStandards\Rules\PortPlacement\PortPlacementRule;
use Innis\CodingStandards\Rules\PortPlacement\ServiceImplementorCollector;
use Innis\CodingStandards\Support\DeliberateFence;
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
            new PortInterfaceCollector(new DeliberateFence()),
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
