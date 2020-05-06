<?php

namespace Tests\App\Security\Voter\Committee;

use App\Committee\CommitteePermissions;
use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\CommitteeFeedItem;
use App\Security\Voter\AbstractAdherentVoter;
use App\Security\Voter\Committee\AdministrateCommitteeFeedItemVoter;
use PHPUnit_Framework_MockObject_MockObject;
use Tests\App\Security\Voter\AbstractAdherentVoterTest;

class AdministrateCommitteeFeedItemVoterTest extends AbstractAdherentVoterTest
{
    public function provideAnonymousCases(): iterable
    {
        yield [false, true, CommitteePermissions::ADMIN_FEED, $this->createMock(CommitteeFeedItem::class)];
    }

    protected function getVoter(): AbstractAdherentVoter
    {
        return new AdministrateCommitteeFeedItemVoter();
    }

    public function testAuthorGranted(): void
    {
        $adherent = $this->getAdherentMock(true);
        $committeeFeed = $this->getCommitteeFeedMock($adherent);

        $this->assertGrantedForAdherent(true, true, $adherent, CommitteePermissions::ADMIN_FEED, $committeeFeed);
    }

    public function testAdherentNotGrantedGranted(): void
    {
        $committee = $this->getCommitteeMock();
        $adherent = $this->getAdherentMock();
        $committeeFeed = $this->getCommitteeFeedMock($adherent, $committee);

        $this->assertGrantedForAdherent(false, true, $adherent, CommitteePermissions::ADMIN_FEED, $committeeFeed);
    }

    public function testSupervisorGranted(): void
    {
        $committee = $this->getCommitteeMock();
        $adherent = $this->createAdherentMock();
        $adherent
            ->expects($this->once())
            ->method('isSupervisorOf')
            ->with($committee)
            ->willReturn(true)
        ;
        $committeeFeed = $this->getCommitteeFeedMock($this->getAdherentMock(), $committee);

        $this->assertGrantedForAdherent(true, true, $adherent, CommitteePermissions::ADMIN_FEED, $committeeFeed);
    }

    private function getCommitteeMock(): Committee
    {
        return $this->createMock(Committee::class);
    }

    private function getCommitteeFeedMock(Adherent $adherent = null, Committee $committee = null): CommitteeFeedItem
    {
        $committeeFeed = $this->createMock(CommitteeFeedItem::class);
        if ($committee) {
            $committeeFeed->expects($this->once())->method('getCommittee')->willReturn($committee);
        }
        $committeeFeed->expects($this->once())->method('getAuthor')->willReturn($adherent);

        return $committeeFeed;
    }

    /**
     * @return Adherent|PHPUnit_Framework_MockObject_MockObject
     */
    private function getAdherentMock(bool $isEqual = false): Adherent
    {
        $adherent = $this->createAdherentMock();
        $adherent
            ->expects($this->once())
            ->method('equals')
            ->with($adherent)
            ->willReturn($isEqual)
        ;

        return $adherent;
    }
}
