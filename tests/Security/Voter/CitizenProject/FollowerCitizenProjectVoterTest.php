<?php

namespace Tests\App\Security\Voter\CitizenProject;

use App\CitizenProject\CitizenProjectPermissions;
use App\Entity\Adherent;
use App\Entity\CitizenProject;
use App\Entity\CitizenProjectMembership;
use App\Security\Voter\AbstractAdherentVoter;
use App\Security\Voter\CitizenProject\FollowerCitizenProjectVoter;
use Tests\App\Security\Voter\AbstractAdherentVoterTest;

class FollowerCitizenProjectVoterTest extends AbstractAdherentVoterTest
{
    public function provideAnonymousCases(): iterable
    {
        yield 'Anonymous cannot follow projects' => [false, true, CitizenProjectPermissions::FOLLOW, $this->getCitizenProjectMock()];
        yield 'Anonymous cannot unfollow projects' => [false, true, CitizenProjectPermissions::UNFOLLOW, $this->getCitizenProjectMock()];
    }

    protected function getVoter(): AbstractAdherentVoter
    {
        return new FollowerCitizenProjectVoter();
    }

    public function testAdherentCannotFollowCitizenProjectIfAlreadyFollowing()
    {
        $citizenProject = $this->getCitizenProjectMock(true);
        $adherent = $this->getAdherentMock($citizenProject, true);

        $this->assertGrantedForAdherent(false, true, $adherent, CitizenProjectPermissions::FOLLOW, $citizenProject);
    }

    public function testAdherentCanFollowCitizenProject()
    {
        $citizenProject = $this->getCitizenProjectMock(true);
        $adherent = $this->getAdherentMock($citizenProject, false);

        $this->assertGrantedForAdherent(true, true, $adherent, CitizenProjectPermissions::FOLLOW, $citizenProject);
    }

    public function testAdherentCannotUnFollowCitizenProjectIfNotFollower()
    {
        $citizenProject = $this->getCitizenProjectMock(true);
        $adherent = $this->getAdherentMock($citizenProject, false);

        $this->assertGrantedForAdherent(false, true, $adherent, CitizenProjectPermissions::UNFOLLOW, $citizenProject);
    }

    public function testAdherentCannotUnFollowCitizenProjectIfAdministrator()
    {
        $citizenProject = $this->getCitizenProjectMock(true);
        $adherent = $this->getAdherentMock($citizenProject, true, true);

        $this->assertGrantedForAdherent(false, true, $adherent, CitizenProjectPermissions::UNFOLLOW, $citizenProject);
    }

    public function testAdherentCanUnFollowCitizenProject()
    {
        $citizenProject = $this->getCitizenProjectMock(true);
        $adherent = $this->getAdherentMock($citizenProject, true, false);

        $this->assertGrantedForAdherent(true, true, $adherent, CitizenProjectPermissions::UNFOLLOW, $citizenProject);
    }

    /**
     * @param CitizenProject|null $project
     *
     * @return Adherent|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getAdherentMock(
        CitizenProject $project,
        bool $isFollower = null,
        bool $isAdministrator = null
    ): Adherent {
        $adherent = $this->createAdherentMock();

        $adherent->expects($this->once())
            ->method('getCitizenProjectMembershipFor')
            ->with($project)
            ->willReturn($this->getMembershipMock($isFollower, $isAdministrator))
        ;

        return $adherent;
    }

    /**
     * @return CitizenProjectMembership|\PHPUnit_Framework_MockObject_MockObject|null
     */
    private function getMembershipMock(bool $isFollower, ?bool $isAdministrator): ?CitizenProjectMembership
    {
        if (!$isFollower) {
            return null;
        }

        $membership = $this->createMock(CitizenProjectMembership::class);

        if (null !== $isAdministrator) {
            $membership->expects($this->once())
                ->method('isAdministrator')
                ->willReturn($isAdministrator)
            ;

            if ($isAdministrator) {
                $membership->expects($this->never())
                    ->method('isFollower')
                ;
            } else {
                $membership->expects($this->once())
                    ->method('isFollower')
                    ->willReturn(true)
                ;
            }
        }

        return $membership;
    }

    /**
     * @param bool $approved
     *
     * @return CitizenProject|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getCitizenProjectMock(bool $approved = null): CitizenProject
    {
        $project = $this->createMock(CitizenProject::class);

        if (null !== $approved) {
            $project->expects($this->once())
                ->method('isApproved')
                ->willReturn($approved)
            ;
        } else {
            $project->expects($this->never())
                ->method('isApproved')
            ;
        }

        return $project;
    }
}
