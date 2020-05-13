<?php

namespace Tests\App\Security\Voter;

use App\Security\Voter\AbstractAdherentVoter;
use App\Security\Voter\MembershipVoter;

class MembershipVoterTest extends AbstractAdherentVoterTest
{
    protected function getVoter(): AbstractAdherentVoter
    {
        return new MembershipVoter();
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
}
