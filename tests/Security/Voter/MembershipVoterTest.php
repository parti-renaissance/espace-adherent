<?php

namespace Tests\AppBundle\Security\Voter;

use AppBundle\Membership\MembershipPermissions;
use AppBundle\Security\Voter\AbstractAdherentVoter;
use AppBundle\Security\Voter\MembershipVoter;

class MembershipVoterTest extends AbstractAdherentVoterTest
{
    protected function getVoter(): AbstractAdherentVoter
    {
        return new MembershipVoter();
    }

    public function provideAnonymousCases(): iterable
    {
        yield [false, true, MembershipPermissions::UNREGISTER];
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

        $this->assertGrantedForAdherent($granted, true, $adherent, MembershipPermissions::UNREGISTER);
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

        $this->assertGrantedForUser($granted, true, $adherent, MembershipPermissions::UNREGISTER);
    }

    public function provideUserCases(): iterable
    {
        yield [true];
        yield [false];
    }
}
