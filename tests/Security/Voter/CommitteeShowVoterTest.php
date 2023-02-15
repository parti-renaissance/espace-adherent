<?php

namespace Tests\App\Security\Voter;

use App\Committee\CommitteePermissions;
use App\Entity\Adherent;
use App\Entity\Committee;
use App\Security\Voter\AbstractAdherentVoter;
use App\Security\Voter\CommitteeShowVoter;
use PHPUnit\Framework\MockObject\MockObject;
use Ramsey\Uuid\UuidInterface;

class CommitteeShowVoterTest extends AbstractAdherentVoterTest
{
    protected function getVoter(): AbstractAdherentVoter
    {
        return new CommitteeShowVoter();
    }

    public function provideAnonymousCases(): iterable
    {
        // Not approved groups should been shown to anonymous
        $notApprovedCommittee = $this->getCommitteeMock(false);

        yield [false, true, CommitteePermissions::SHOW, $notApprovedCommittee];

        // Approved groups should be shown to anonymous
        $approvedCommittee = $this->getCommitteeMock(true);

        yield [true, false, CommitteePermissions::SHOW, $approvedCommittee];
    }

    /**
     * @dataProvider provideGroupCases
     */
    public function testAdherentIsGrantedIfGroupIsApproved(bool $approved, string $attribute)
    {
        $adherent = $this->getAdherentMock(!$approved);
        $group = $this->getCommitteeMock($approved, false);

        $this->assertGrantedForAdherent($approved, !$approved, $adherent, $attribute, $group);
    }

    /**
     * @dataProvider provideGroupCases
     */
    public function testAdherentIsGrantedWhenGroupIsNotApprovedIfCreator(bool $approved, string $attribute)
    {
        $adherent = $this->getAdherentMock(!$approved);
        $group = $this->getCommitteeMock($approved, true);

        $this->assertGrantedForAdherent(true, !$approved, $adherent, $attribute, $group);
    }

    public function provideGroupCases(): iterable
    {
        yield [true, CommitteePermissions::SHOW];
        yield [false, CommitteePermissions::SHOW];
    }

    /**
     * @return Adherent|MockObject
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
     * @return Committee|MockObject
     */
    private function getCommitteeMock(bool $approved, bool $withCreator = null): Committee
    {
        $group = $this->createMock(Committee::class);

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
