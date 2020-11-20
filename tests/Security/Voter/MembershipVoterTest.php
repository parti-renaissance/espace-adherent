<?php

namespace Tests\App\Security\Voter;

use App\Repository\AdherentMandate\AdherentMandateRepository;
use App\Security\Voter\AbstractAdherentVoter;
use App\Security\Voter\MembershipVoter;

class MembershipVoterTest extends AbstractAdherentVoterTest
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|AdherentMandateRepository */
    private $adherentMandateRepository;

    protected function getVoter(): AbstractAdherentVoter
    {
        $this->adherentMandateRepository = $this->createMock(AdherentMandateRepository::class);

        return new MembershipVoter($this->adherentMandateRepository);
    }

    public function provideAnonymousCases(): iterable
    {
        yield [false, true, MembershipVoter::PERMISSION_UNREGISTER];
    }

    /**
     * @dataProvider provideBasicAdherentCases
     */
    public function testAdherentIsGrantedIfBasic(bool $granted)
    {
        $adherent = $this->createAdherentMock();

        $adherent->expects($this->once())
            ->method('isBasicAdherent')
            ->willReturn($granted)
        ;

        if (!$granted) {
            $adherent->expects($this->once())
                ->method('isUser')
                ->willReturn(false)
            ;
        } else {
            $this->adherentMandateRepository
                ->expects($this->once())
                ->method('hasActiveMandate')
                ->with($adherent)
                ->willReturn(false)
            ;
        }

        $this->assertGrantedForAdherent($granted, true, $adherent, MembershipVoter::PERMISSION_UNREGISTER);
    }

    public function provideBasicAdherentCases(): iterable
    {
        yield [true];
        yield [false];
    }

    /**
     * @dataProvider provideUserCases
     */
    public function testAdherentIsGrantedIfUser(bool $granted)
    {
        $adherent = $this->createAdherentMock();

        $adherent->expects($this->once())
            ->method('isBasicAdherent')
            ->willReturn(false)
        ;

        $adherent->expects($this->once())
            ->method('isUser')
            ->willReturn($granted)
        ;

        $this->assertGrantedForUser($granted, true, $adherent, MembershipVoter::PERMISSION_UNREGISTER);
    }

    public function provideUserCases(): iterable
    {
        yield [true];
        yield [false];
    }

    /**
     * @dataProvider provideWithActiveMandateCases
     */
    public function testAdherentIsGrantedIfActiveMandates(bool $granted)
    {
        $adherent = $this->createAdherentMock();

        $adherent->expects($this->once())
            ->method('isBasicAdherent')
            ->willReturn(true)
        ;

        $this->adherentMandateRepository
            ->expects($this->once())
            ->method('hasActiveMandate')
            ->with($adherent)
            ->willReturn($granted)
        ;

        $this->assertGrantedForAdherent(!$granted, true, $adherent, MembershipVoter::PERMISSION_UNREGISTER);
    }

    public function provideWithActiveMandateCases(): iterable
    {
        yield [true];
        yield [false];
    }
}
