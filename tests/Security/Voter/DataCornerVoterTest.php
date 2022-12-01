<?php

namespace Tests\App\Security\Voter;

use App\Entity\Adherent;
use App\Repository\ScopeRepository;
use App\Scope\GeneralScopeGenerator;
use App\Scope\Scope;
use App\Scope\ScopeEnum;
use App\Security\Voter\DataCornerVoter;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class DataCornerVoterTest extends AbstractAdherentVoterTest
{
    private $scopeGeneratorMock;

    public function provideAnonymousCases(): iterable
    {
        yield [false, true, DataCornerVoter::DATA_CORNER];
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

    /**
     * @dataProvider provideAdherent
     */
    public function testAdherentIsGranted(bool $isDeputy, bool $isSenator, bool $isGranted): void
    {
        $adherent = $this->getAdherentMock($isDeputy, $isSenator);

        $this->scopeGeneratorMock->expects($this->once())
            ->method('generateScopes')
            ->willReturn(
                 ($isDeputy ? [new Scope('deputy', 'Député', [], [], [])] : [])
                 + ($isSenator ? [new Scope('senator', 'Sénateur', [], [], [])] : [])
            )
        ;

        $this->assertGrantedForAdherent($isGranted, true, $adherent, DataCornerVoter::DATA_CORNER, $adherent);
    }

    public function provideAdherent(): iterable
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
