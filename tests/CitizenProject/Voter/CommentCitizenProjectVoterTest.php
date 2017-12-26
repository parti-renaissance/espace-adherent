<?php

namespace Tests\AppBundle\CitizenProject\Voter;

use AppBundle\CitizenProject\CitizenProjectPermissions;
use AppBundle\CitizenProject\Voter\CommentCitizenProjectVoter;
use AppBundle\Entity\CitizenProjectCategory;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class CommentCitizenProjectVoterTest extends AbstractCitizenProjectVoterTest
{
    /* @var CommentCitizenProjectVoter */
    private $voter;
    private $manager;

    private const PERMISSIONS = [
        CitizenProjectPermissions::COMMENT,
        CitizenProjectPermissions::SHOW_COMMENT,
    ];

    public function testCitizenProjectMemberCanComment()
    {
        $adherent = $this->createAdherentFromUuidAndEmail(self::ADHERENT_1_UUID);
        $citizenProject = $this->createCitizenProject(self::ADHERENT_1_UUID, $this->createCategory());
        $adherent->followCitizenProject($citizenProject);
        $token = $this->createAuthenticatedToken($adherent);

        $this->assertPermissions(VoterInterface::ACCESS_GRANTED, $token, $citizenProject);
    }

    public function testCitizenProjectNonMemberCannotComment()
    {
        $adherent = $this->createAdherentFromUuidAndEmail(self::ADHERENT_1_UUID);
        $citizenProject = $this->createCitizenProject(self::ADHERENT_1_UUID, $this->createCategory());
        $token = $this->createAuthenticatedToken($adherent);

        $this->assertPermissions(VoterInterface::ACCESS_DENIED, $token, $citizenProject);
    }

    public function testAnonymousCannotCommentCitizenProject()
    {
        $citizenProject = $this->createCitizenProject(self::ADHERENT_2_UUID, $this->createCategory());
        $token = $this->createAnonymousToken();

        $this->assertPermissions(VoterInterface::ACCESS_DENIED, $token, $citizenProject);
    }

    private function assertPermissions($accessLevel, $token, $citizenProject): void
    {
        foreach (self::PERMISSIONS as $permission) {
            $this->assertSame($accessLevel, $this->voter->vote($token, $citizenProject, [$permission]));
        }
    }

    private function createCategory(): CitizenProjectCategory
    {
        return new CitizenProjectCategory(self::CATEGORY_1);
    }

    protected function setUp()
    {
        parent::setUp();

        $this->voter = new CommentCitizenProjectVoter();
    }

    protected function tearDown()
    {
        $this->manager = null;
        $this->voter = null;

        parent::tearDown();
    }
}
