<?php

namespace Tests\AppBundle\Security\Voter\CitizenProject;

use AppBundle\CitizenProject\CitizenProjectPermissions;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\CitizenProject;
use AppBundle\Entity\CitizenProjectMembership;
use AppBundle\Security\Voter\AbstractAdherentVoter;
use AppBundle\Security\Voter\CitizenProject\CommentsCitizenProjectVoter;
use Tests\AppBundle\Security\Voter\AbstractAdherentVoterTest;

class CommentsCitizenProjectVoterTest extends AbstractAdherentVoterTest
{
    public function provideAnonymousCases(): iterable
    {
        // Anonymous should not be granted to comment projects, approved or not
        foreach (CitizenProjectPermissions::COMMENTS as $permission) {
            yield [false, true, $permission, $this->getCitizenProjectMock(true)];
        }

        foreach (CitizenProjectPermissions::COMMENTS as $permission) {
            yield [false, false, $permission, $this->getCitizenProjectMock(false)];
        }
    }

    protected function getVoter(): AbstractAdherentVoter
    {
        return new CommentsCitizenProjectVoter();
    }

    /**
     * @dataProvider provideCommentsCases
     */
    public function testCitizenProjectMemberCanCommentIfProjectApproved(
        string $attribute,
        bool $approved,
        bool $isMember
    ) {
        $project = $this->getCitizenProjectMock($approved);
        $adherent = $this->getAdherentMock($approved, $project, $isMember);

        $this->assertGrantedForAdherent($approved && $isMember, $approved, $adherent, $attribute, $project);
    }

    public function provideCommentsCases(): iterable
    {
        foreach (CitizenProjectPermissions::COMMENTS as $permission) {
            yield [$permission, true, false];
            yield [$permission, true, true];
            yield [$permission, false, true];
            yield [$permission, false, false];
        }
    }

    /**
     * @return Adherent|\PHPUnit_Framework_MockObject_MockObject
     */
    public function getAdherentMock(bool $membershipChecked, CitizenProject $project, bool $isMember): Adherent
    {
        $adherent = $this->createAdherentMock();

        if ($membershipChecked) {
            $adherent->expects($this->once())
                ->method('getCitizenProjectMembershipFor')
                ->with($project)
                ->willReturn($isMember ? $this->createMock(CitizenProjectMembership::class) : null)
            ;
        } else {
            $adherent->expects($this->never())
                ->method('getCitizenProjectMembershipFor')
            ;
        }

        return $adherent;
    }

    /**
     * @return CitizenProject|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getCitizenProjectMock(bool $approved, bool $doubleChecked = false): CitizenProject
    {
        $project = $this->createMock(CitizenProject::class);

        $project->expects($doubleChecked ? $this->exactly(2) : $this->once())
            ->method('isApproved')
            ->willReturn($approved)
        ;

        return $project;
    }
}
