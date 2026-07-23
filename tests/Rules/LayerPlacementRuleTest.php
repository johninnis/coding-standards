<?php

declare(strict_types=1);

namespace Innis\CodingStandards\Tests\Rules;

use Innis\CodingStandards\Rules\LayerPlacementRule;
use Innis\CodingStandards\Support\DeliberateFence;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<LayerPlacementRule>
 */
final class LayerPlacementRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new LayerPlacementRule(new DeliberateFence());
    }

    public function testFlagsAnUnlayeredClassImplementingALayeredContract(): void
    {
        $this->analyse([__DIR__.'/../data/LayerPlacement/Lifecycle.php'], [
            ['HostLifecycle implements the Application contract Acme\Kernel\Application\Port\LifecycleInterface but sits outside the layers; only composition belongs there — file it under a layer so its dependencies are checked.', 9],
        ]);
    }

    public function testFlagsAnUnlayeredClassExtendingALayeredParent(): void
    {
        $this->analyse([__DIR__.'/../data/LayerPlacement/Inheriting.php'], [
            ['HostSigner extends the Domain contract Acme\Kernel\Domain\Service\Signer but sits outside the layers; only composition belongs there — file it under a layer so its dependencies are checked.', 9],
        ]);
    }

    public function testAcceptsAContainerThatOnlyConstructs(): void
    {
        $this->analyse([__DIR__.'/../data/LayerPlacement/Container.php'], []);
    }

    public function testAcceptsAContractCarryingNoLayerSegment(): void
    {
        $this->analyse([__DIR__.'/../data/LayerPlacement/Extension.php'], []);
    }

    public function testSaysNothingAboutAClassAlreadyFiledUnderALayer(): void
    {
        $this->analyse([__DIR__.'/../data/LayerPlacement/Layered.php'], []);
    }

    public function testAcceptsAnAdrFencedDeparture(): void
    {
        $this->analyse([__DIR__.'/../data/LayerPlacement/Fenced.php'], []);
    }
}
