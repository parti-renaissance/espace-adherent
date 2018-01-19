<?php

namespace Tests\AppBundle\Security\Voter\Committee;

use AppBundle\Committee\CommitteePermissions;
use AppBundle\Security\Voter\Committee\ShowGroupVoter;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\User\User;

class ShowCommitteeVoterTest extends AbstractCommitteeVoterTest
{
    /* @var ShowGroupVoter */
    private $voter;

    public function testUnsupportedAdherentType()
    {
        $committee = $this->createCommittee(self::ADHERENT_2_UUID);
        $token = $this->createAuthenticatedToken(new User('foobar', 'password', ['ROLE_USER', 'ROLE_ADHERENT']));

        $this->assertSame(
            VoterInterface::ACCESS_DENIED,
            $this->voter->vote($token, $committee, [CommitteePermissions::SHOW])
        );
    }

    public function testUnsupportedCommitteeType()
    {
        $token = $this->createAuthenticatedToken($this->createAdherentFromUuidAndEmail(self::ADHERENT_2_UUID));

        $this->assertSame(
            VoterInterface::ACCESS_ABSTAIN,
            $this->voter->vote($token, new \stdClass(), [CommitteePermissions::SHOW])
        );
    }

    public function testUnsupportedAttribute()
    {
        $adherent = $this->createAdherentFromUuidAndEmail(self::ADHERENT_1_UUID);
        $committee = $this->createCommittee(self::ADHERENT_1_UUID);
        $token = $this->createAuthenticatedToken($adherent);

        $this->assertSame(
            VoterInterface::ACCESS_ABSTAIN,
            $this->voter->vote($token, $committee, [CommitteePermissions::CREATE])
        );
    }

    public function testAnonymousIsGrantedToShowAnyApprovedCommittee()
    {
        $committee = $this->createCommittee(self::ADHERENT_1_UUID);
        $committee->approved();

        $token = $this->createAnonymousToken();

        $this->assertSame(
            VoterInterface::ACCESS_GRANTED,
            $this->voter->vote($token, $committee, [CommitteePermissions::SHOW])
        );
    }

    public function testAnyAdherentIsGrantedToShowAnyApprovedCommittee()
    {
        $adherent = $this->createAdherentFromUuidAndEmail(self::ADHERENT_1_UUID);

        $committee = $this->createCommittee(self::ADHERENT_2_UUID);
        $committee->approved();

        $token = $this->createAuthenticatedToken($adherent);

        $this->assertSame(
            VoterInterface::ACCESS_GRANTED,
            $this->voter->vote($token, $committee, [CommitteePermissions::SHOW])
        );
    }

    public function testCommitteeOwnerIsGrantedToShowHisUnapprovedCommittee()
    {
        $adherent = $this->createAdherentFromUuidAndEmail(self::ADHERENT_1_UUID);
        $committee = $this->createCommittee(self::ADHERENT_2_UUID);
        $token = $this->createAuthenticatedToken($adherent);

        $this->assertSame(
            VoterInterface::ACCESS_DENIED,
            $this->voter->vote($token, $committee, [CommitteePermissions::SHOW])
        );
    }

    public function testCommitteeForeignerIsDeniedToShowUnapprovedCommittee()
    {
        $adherent = $this->createAdherentFromUuidAndEmail(self::ADHERENT_1_UUID);
        $committee = $this->createCommittee(self::ADHERENT_1_UUID);
        $token = $this->createAuthenticatedToken($adherent);

        $this->assertSame(
            VoterInterface::ACCESS_GRANTED,
            $this->voter->vote($token, $committee, [CommitteePermissions::SHOW])
        );
    }

    protected function setUp()
    {
        parent::setUp();

        $this->voter = new ShowGroupVoter();
    }

    protected function tearDown()
    {
        $this->voter = null;

        parent::tearDown();
    }
}
