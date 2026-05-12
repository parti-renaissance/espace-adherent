<?php

declare(strict_types=1);

namespace Tests\App\Controller\EnMarche\VotingPlatform;

use App\DataFixtures\ORM\LoadVotingPlatformElectionData;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;
use Tests\App\AbstractEnMarcheWebTestCase;
use Tests\App\Controller\ControllerTestTrait;

#[Group('functional')]
#[Group('voting-platform')]
class ConsultationAdherentVotingPlatformControllerTest extends AbstractEnMarcheWebTestCase
{
    use ControllerTestTrait;

    private const ELECTION_VOTE_URI = '/elections/'.LoadVotingPlatformElectionData::ELECTION_UUID14.'/vote';

    /**
     * Laura (adherent-9) is registered in the consultation voters list but is no longer up to
     * date with her membership: she must still be able to access the vote.
     */
    public function testAdherentInTheVotersListCanVoteEvenWithoutUpToDateMembership(): void
    {
        $this->authenticateAsAdherent($this->client, 'laura@deloche.com');

        $this->client->request(Request::METHOD_GET, self::ELECTION_VOTE_URI);

        $this->isSuccessful($this->client->getResponse());
    }

    public function testAdherentNotInTheVotersListCannotVote(): void
    {
        $this->authenticateAsAdherent($this->client, 'gisele-berthoux@caramail.com');

        $this->client->request(Request::METHOD_GET, self::ELECTION_VOTE_URI);

        $this->assertClientIsRedirectedTo('//vox.code/', $this->client);
    }
}
