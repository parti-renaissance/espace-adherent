<?php

namespace Tests\AppBundle\Security\Voter\Committee;

use AppBundle\Committee\CommitteePermissions;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;
use AppBundle\Entity\CommitteeMembership;
use AppBundle\Repository\CommitteeMembershipRepository;
use AppBundle\Security\Voter\AbstractAdherentVoter;
use AppBundle\Security\Voter\Committee\FollowerCommitteeVoter;
use Ramsey\Uuid\UuidInterface;
use Tests\AppBundle\Security\Voter\AbstractAdherentVoterTest;

class FollowCommitteeVoterTest extends AbstractAdherentVoterTest
{
    /**
     * @var CommitteeMembershipRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    private $membershipRepository;

    protected function setUp(): void
    {
        $this->membershipRepository = $this->createMock(CommitteeMembershipRepository::class);

        parent::setUp();
    }

    protected function tearDown(): void
    {
        $this->membershipRepository = null;

        parent::tearDown();
    }

    public function provideAnonymousCases(): iterable
    {
        yield 'Anonymous cannot follow committees' => [false, true, CommitteePermissions::FOLLOW, $this->getCommitteeMock()];
        yield 'Anonymous cannot unfollow committees' => [false, true, CommitteePermissions::UNFOLLOW, $this->getCommitteeMock()];
    }

    public function testAdherentCannotFollowCommitteeIfAlreadyFollowing()
    {
        $committee = $this->getCommitteeMock(true);
        $adherent = $this->getAdherentMock($committee, true);

        $this->assertRepositoryBehavior(null);
        $this->assertGrantedForAdherent(false, true, $adherent, CommitteePermissions::FOLLOW, $committee);
    }

    public function testAdherentCannotFollowCommitteeIfNotApproved()
    {
        $committee = $this->getCommitteeMock(false);
        $adherent = $this->getAdherentMock();

        $this->assertRepositoryBehavior(null);
        $this->assertGrantedForAdherent(false, true, $adherent, CommitteePermissions::FOLLOW, $committee);
    }

    public function testAdherentCanFollowCommittee()
    {
        $committee = $this->getCommitteeMock(true);
        $adherent = $this->getAdherentMock($committee);

        $this->assertRepositoryBehavior(null);
        $this->assertGrantedForAdherent(true, true, $adherent, CommitteePermissions::FOLLOW, $committee);
    }

    public function testAdherentCannotUnFollowCommitteeIfNotAlreadyFollowing()
    {
        $committee = $this->getCommitteeMock(true);
        $adherent = $this->getAdherentMock($committee);

        $this->assertRepositoryBehavior(null);
        $this->assertGrantedForAdherent(false, true, $adherent, CommitteePermissions::UNFOLLOW, $committee);
    }

    public function testAdherentCannotUnfollowCommitteeIfNotApproved()
    {
        $committee = $this->getCommitteeMock(false);
        $adherent = $this->getAdherentMock();

        $this->assertRepositoryBehavior(null);
        $this->assertGrantedForAdherent(false, true, $adherent, CommitteePermissions::UNFOLLOW, $committee);
    }

    public function testSupervisorCannotUnfollowCommittee()
    {
        $committee = $this->getCommitteeMock(true);
        $adherent = $this->getAdherentMock($committee, true, true);

        $this->assertRepositoryBehavior(null);
        $this->assertGrantedForAdherent(false, true, $adherent, CommitteePermissions::UNFOLLOW, $committee);
    }

    public function testAdherentCanUnfollowCommittee()
    {
        $committee = $this->getCommitteeMock(true);
        $adherent = $this->getAdherentMock($committee, true, false);

        $this->assertRepositoryBehavior(null);
        $this->assertGrantedForAdherent(true, true, $adherent, CommitteePermissions::UNFOLLOW, $committee);
    }

    public function testHostCannotUnfollowCommitteeIfOnlyHost()
    {
        $committee = $this->getCommitteeMock(true);
        $adherent = $this->getAdherentMock($committee, true, false, true);

        $this->assertRepositoryBehavior(false);
        $this->assertGrantedForAdherent(false, true, $adherent, CommitteePermissions::UNFOLLOW, $committee);
    }

    public function testHostCanUnfollowCommitteeIfManyHosts()
    {
        $committee = $this->getCommitteeMock(true);
        $adherent = $this->getAdherentMock($committee, true, false, true);

        $this->assertRepositoryBehavior(true);
        $this->assertGrantedForAdherent(true, true, $adherent, CommitteePermissions::UNFOLLOW, $committee);
    }

    protected function getVoter(): AbstractAdherentVoter
    {
        return new FollowerCommitteeVoter($this->membershipRepository);
    }

    /**
     * @return Adherent|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getAdherentMock(
        Committee $committee = null,
        bool $isFollower = null,
        bool $isSupervisor = null,
        bool $isHost = false
    ): Adherent {
        $adherent = $this->createAdherentMock();

        if ($committee) {
            $adherent->expects($this->once())
                ->method('getMembershipFor')
                ->with($committee)
                ->willReturn($this->getMembershipMock($isFollower, $isSupervisor, $isHost))
            ;
        } else {
            $adherent->expects($this->never())
                ->method('getMembershipFor')
            ;
        }

        return $adherent;
    }

    /**
     * @return CommitteeMembership|\PHPUnit_Framework_MockObject_MockObject|null
     */
    private function getMembershipMock(
        ?bool $isFollower,
        ?bool $isSupervisor,
        bool $isHost = false
    ): ?CommitteeMembership {
        if (!$isFollower) {
            return null;
        }

        $membership = $this->createMock(CommitteeMembership::class);

        if (null !== $isSupervisor) {
            $membership->expects($this->once())
                ->method('isSupervisor')
                ->willReturn($isSupervisor)
            ;

            if ($isSupervisor) {
                $membership->expects($this->never())
                    ->method('isFollower')
                ;
            } else {
                $membership->expects($this->once())
                    ->method('isFollower')
                    ->willReturn(!$isHost)
                ;
            }
        } else {
            $membership->expects($this->never())
                ->method('isSupervisor')
            ;
        }

        return $membership;
    }

    /**
     * @param bool $approved
     *
     * @return Committee|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getCommitteeMock(bool $approved = null): Committee
    {
        $committee = $this->createMock(Committee::class);

        if (null !== $approved) {
            $committee->expects($this->once())
                ->method('isApproved')
                ->willReturn($approved)
            ;
        } else {
            $committee->expects($this->never())
                ->method('isApproved')
            ;
        }

        $uuid = $this->createMock(UuidInterface::class);
        $uuid->expects($this->any())
            ->method('toString')
            ->willReturn('test')
        ;

        $committee->expects($this->any())
            ->method('getUuid')
            ->willReturn($uuid)
        ;

        return $committee;
    }

    private function assertRepositoryBehavior(?bool $manyHost): void
    {
        if (null !== $manyHost) {
            $this->membershipRepository->expects($this->once())
                ->method('countHostMembers')
                ->with($this->isInstanceOf(Committee::class))
                ->willReturn($manyHost ? 2 : 1)
            ;
        } else {
            $this->membershipRepository->expects($this->never())
                ->method('countHostMembers')
            ;
        }
    }
}
