<?php

declare(strict_types=1);

namespace Tests\App\Scope\Generator;

use App\Entity\Adherent;
use App\Entity\Scope as ScopeEntity;
use App\Repository\ScopeRepository;
use App\Scope\AppEnum;
use App\Scope\FeatureEnum;
use App\Scope\Generator\MilitantScopeGenerator;
use App\Scope\Scope;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

class MilitantScopeGeneratorTest extends TestCase
{
    public function testSupportsReturnsTrueForAnyAdherent(): void
    {
        $generator = $this->createGenerator($this->createStub(ScopeRepository::class));

        self::assertTrue($generator->supports($this->createStub(Adherent::class)));
    }

    public function testGenerateProducesZonelessScope(): void
    {
        $scope = $this->generate([FeatureEnum::EVENTS, FeatureEnum::ACTIONS]);

        self::assertSame([], $scope->getZones());
    }

    public function testGenerateExposesEventsAndActionsFeatures(): void
    {
        $scope = $this->generate([FeatureEnum::EVENTS, FeatureEnum::ACTIONS]);

        self::assertSame([FeatureEnum::EVENTS, FeatureEnum::ACTIONS], $scope->getFeatures());
    }

    public function testGenerateExcludesAdherentDisabledScopeFeatures(): void
    {
        $scope = $this->generate([FeatureEnum::EVENTS, FeatureEnum::ACTIONS], [FeatureEnum::EVENTS]);

        self::assertSame([FeatureEnum::ACTIONS], $scope->getFeatures());
    }

    private function generate(array $features, array $disabledFeatures = []): Scope
    {
        $scopeEntity = new ScopeEntity('militant', 'Militant', $features, [AppEnum::JEMARCHE]);

        $repository = $this->createMock(ScopeRepository::class);
        $repository
            ->expects(self::once())
            ->method('findOneByCode')
            ->with('militant')
            ->willReturn($scopeEntity)
        ;

        $adherent = $this->createStub(Adherent::class);
        $adherent->disabledScopeFeatures = $disabledFeatures;

        return $this->createGenerator($repository)->generate($adherent);
    }

    private function createGenerator(ScopeRepository $repository): MilitantScopeGenerator
    {
        $translator = $this->createStub(TranslatorInterface::class);
        $translator->method('trans')->willReturnArgument(0);

        return new MilitantScopeGenerator($repository, $translator);
    }
}
