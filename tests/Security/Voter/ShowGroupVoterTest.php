<?php

namespace Tests\AppBundle\Security\Voter;

use AppBundle\CitizenProject\CitizenProjectPermissions;
use AppBundle\Committee\CommitteePermissions;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\BaseGroup;
use AppBundle\Entity\CitizenProject;
use AppBundle\Entity\Committee;
use AppBundle\Security\Voter\AbstractAdherentVoter;
use AppBundle\Security\Voter\ShowGroupVoter;
use Ramsey\Uuid\UuidInterface;

class ShowGroupVoterTest extends AbstractAdherentVoterTest
{
    protected function getVoter(): AbstractAdherentVoter
    {
        return new ShowGroupVoter();
    }

    public function provideAnonymousCases(): iterable
    {
        // Not approved groups should been shown to anonymous
        $notApprovedCitizenProject = $this->getGroupMock(CitizenProject::class, false);
        $notApprovedCommittee = $this->getGroupMock(Committee::class, false);

        yield [false, true, CitizenProjectPermissions::SHOW, $notApprovedCitizenProject];
        yield [false, true, CommitteePermissions::SHOW, $notApprovedCommittee];

        // Approved groups should be shown to anonymous
        $approvedCitizenProject = $this->getGroupMock(CitizenProject::class, true);
        $approvedCommittee = $this->getGroupMock(Committee::class, true);

        yield [true, false, CitizenProjectPermissions::SHOW, $approvedCitizenProject];
        yield [true, false, CommitteePermissions::SHOW, $approvedCommittee];
    }

    /**
     * @dataProvider provideGroupCases
     */
    public function testAdherentIsGrantedIfGroupIsApproved(string $groupClass, bool $approved, string $attribute)
    {
        $adherent = $this->getAdherentMock(!$approved);
        $group = $this->getGroupMock($groupClass, $approved, false);

        $this->assertGrantedForAdherent($approved, !$approved, $adherent, $attribute, $group);
    }

    /**
     * @dataProvider provideGroupCases
     */
    public function testAdherentIsGrantedWhenGroupIsNotApprovedIfCreator(
        string $groupClass,
        bool $approved,
        string $attribute
    ) {
        $adherent = $this->getAdherentMock(!$approved);
        $group = $this->getGroupMock($groupClass, $approved, true);

        $this->assertGrantedForAdherent(true, !$approved, $adherent, $attribute, $group);
    }

    public function provideGroupCases(): iterable
    {
        yield [CitizenProject::class, true, CitizenProjectPermissions::SHOW];
        yield [CitizenProject::class, false, CitizenProjectPermissions::SHOW];
        yield [Committee::class, true, CommitteePermissions::SHOW];
        yield [Committee::class, false, CommitteePermissions::SHOW];
    }

    /**
     * @return Adherent|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getAdherentMock(bool $getUuidIsCalled): Adherent
    {
        $adherent = $this->createAdherentMock();

        if ($getUuidIsCalled) {
            $adherent->expects($this->once())
                ->method('getUuid')
                ->willReturn($this->createMock(UuidInterface::class))
            ;
        } else {
            $adherent->expects($this->never())
                ->method('getUuid')
            ;
        }

        return $adherent;
    }

    /**
     * @return BaseGroup|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getGroupMock(string $groupClass, bool $approved, bool $withCreator = null): BaseGroup
    {
        $group = $this->createMock($groupClass);

        $group->expects($this->once())
            ->method('isApproved')
            ->willReturn($approved)
        ;

        if ($approved) {
            $group->expects($this->never())
                ->method('isCreatedBy')
            ;
        } elseif (null !== $withCreator) {
            $group->expects($this->once())
                ->method('isCreatedBy')
                ->with($this->isInstanceOf(UuidInterface::class))
                ->willReturn($withCreator)
            ;
        }

        return $group;
    }
}
