<?php

namespace Tests\App\Security\Voter;

use App\Entity\Adherent;
use App\Repository\ScopeRepository;
use App\Scope\GeneralScopeGenerator;
use App\Scope\Scope;
use App\Scope\ScopeEnum;
use App\Security\Voter\DataCornerVoter;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class DataCornerVoterTest extends AbstractAdherentVoterTestCase
{
    private $scopeGeneratorMock;

    public static function provideAnonymousCases(): iterable
    {
        yield [false, false, DataCornerVoter::DATA_CORNER];
    }

    protected function getVoter(): VoterInterface
    {
        $scopeRepository = $this->createConfiguredMock(ScopeRepository::class, ['findCodesGrantedForDataCorner' => [
            ScopeEnum::SENATOR,
            ScopeEnum::DEPUTY,
        ]]);

        $this->scopeGeneratorMock = $this->createConfiguredMock(GeneralScopeGenerator::class, []);

        return new DataCornerVoter($scopeRepository, $this->scopeGeneratorMock);
    }

    #[DataProvider('provideAdherent')]
    public function testAdherentIsGranted(bool $isDeputy, bool $isSenator, bool $isGranted): void
    {
        $adherent = $this->getAdherentMock($isDeputy, $isSenator);

        $this->scopeGeneratorMock->expects($this->once())
            ->method('generateScopes')
            ->willReturn(
                ($isDeputy ? [new Scope('deputy', 'Délégué de circonscription', [], [], [], $adherent)] : [])
                + ($isSenator ? [new Scope('senator', 'Sénateur', [], [], [], $adherent)] : [])
            )
        ;

        $this->assertGrantedForAdherent($isGranted, false, $adherent, DataCornerVoter::DATA_CORNER, $adherent);
    }

    public static function provideAdherent(): iterable
    {
        yield [true, false, true];
        yield [false, true, true];
        yield [true, true, true];
        yield [false, false, false];
    }

    private function getAdherentMock(bool $isDeputy = false, bool $isSenator = false): Adherent
    {
        $adherent = $this->createAdherentMock();

        $adherent->expects($this->any())
            ->method('isRenaissanceUser')
            ->willReturn(true)
        ;
        $adherent->expects($this->any())
            ->method('isDeputy')
            ->willReturn($isDeputy)
        ;
        $adherent->expects($this->any())
            ->method('isSenator')
            ->willReturn($isSenator)
        ;

        return $adherent;
    }
}
