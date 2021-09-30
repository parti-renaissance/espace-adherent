<?php

namespace Tests\App\Security\Voter;

use App\Entity\Adherent;
use App\Repository\ScopeRepository;
use App\Scope\ScopeEnum;
use App\Security\Voter\AbstractAdherentVoter;
use App\Security\Voter\DataCornerVoter;

class DataCornerVoterTest extends AbstractAdherentVoterTest
{
    public function provideAnonymousCases(): iterable
    {
        yield [false, true, DataCornerVoter::DATA_CORNER];
    }

    protected function getVoter(): AbstractAdherentVoter
    {
        $scopeRepository = $this->createConfiguredMock(ScopeRepository::class, ['findCodesGrantedForDataCorner' => [
            ScopeEnum::SENATOR,
            ScopeEnum::DEPUTY,
        ]]);

        return new DataCornerVoter($scopeRepository);
    }

    /**
     * @dataProvider provideAdherent
     */
    public function testAdherentIsGranted(bool $isDeputy, bool $isSenator, bool $isGranted): void
    {
        $adherent = $this->getAdherentMock($isDeputy, $isSenator);

        $this->assertGrantedForAdherent($isGranted, true, $adherent, DataCornerVoter::DATA_CORNER);
    }

    public function provideAdherent(): iterable
    {
        yield [true, false, true];
        yield [false, true, true];
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
