<?php

declare(strict_types=1);

namespace Innis\CodingStandards\Tests\Rules;

use Innis\CodingStandards\Rules\ParameterCountRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<ParameterCountRule>
 */
final class ParameterCountRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new ParameterCountRule();
    }

    public function testFlagsMoreThanThreeParameters(): void
    {
        $this->analyse([__DIR__.'/../data/Params/TooMany.php'], [
            ['build() takes 4 parameters; more than 3 is a design signal — decompose the unit rather than bundling arguments into a parameter object.', 7],
            ['handle() takes 4 parameters; more than 3 is a design signal — decompose the unit rather than bundling arguments into a parameter object.', 14],
        ]);
    }

    public function testADeliberateFenceDoesNotSilenceIt(): void
    {
        $this->analyse([__DIR__.'/../data/Params/Fenced.php'], [
            ['fenced() takes 4 parameters; more than 3 is a design signal — decompose the unit rather than bundling arguments into a parameter object.', 10],
        ]);
    }

    public function testExemptsValueObjectConstructorAndStaticNamedConstructorsOnly(): void
    {
        $this->analyse([__DIR__.'/../data/Params/ValueObjectConstruction.php'], [
            ['withLines() takes 4 parameters; more than 3 is a design signal — decompose the unit rather than bundling arguments into a parameter object.', 28],
            ['format() takes 4 parameters; more than 3 is a design signal — decompose the unit rather than bundling arguments into a parameter object.', 33],
        ]);
    }

    public function testExemptsEntityConstructor(): void
    {
        $this->analyse([__DIR__.'/../data/Params/EntityConstruction.php'], []);
    }

    public function testExemptsDtoConstructionButNotOtherMethods(): void
    {
        $this->analyse([__DIR__.'/../data/Params/DtoConstruction.php'], [
            ['describe() takes 4 parameters; more than 3 is a design signal — decompose the unit rather than bundling arguments into a parameter object.', 18],
        ]);
    }

    public function testExemptsViewModelConstruction(): void
    {
        $this->analyse([__DIR__.'/../data/Params/ViewModelConstruction.php'], []);
    }

    public function testFlagsDtoNamespaceClassThatIsNotReadonly(): void
    {
        $this->analyse([__DIR__.'/../data/Params/MutableDto.php'], [
            ['__construct() takes 4 parameters; more than 3 is a design signal — decompose the unit rather than bundling arguments into a parameter object.', 9],
        ]);
    }

    public function testFlagsServiceConstructorThatOnlyPromotesReadonlyDependencies(): void
    {
        $this->analyse([__DIR__.'/../data/Params/NotAValueObject.php'], [
            ['__construct() takes 4 parameters; more than 3 is a design signal — decompose the unit rather than bundling arguments into a parameter object.', 9],
        ]);
    }

    public function testFlagsValueObjectNamespaceClassThatIsNotReadonly(): void
    {
        $this->analyse([__DIR__.'/../data/Params/MutableValueObject.php'], [
            ['__construct() takes 4 parameters; more than 3 is a design signal — decompose the unit rather than bundling arguments into a parameter object.', 9],
        ]);
    }

    public function testFlagsValueObjectNamespaceClassThatIsNotFinal(): void
    {
        $this->analyse([__DIR__.'/../data/Params/OpenValueObject.php'], [
            ['__construct() takes 4 parameters; more than 3 is a design signal — decompose the unit rather than bundling arguments into a parameter object.', 9],
        ]);
    }
}
