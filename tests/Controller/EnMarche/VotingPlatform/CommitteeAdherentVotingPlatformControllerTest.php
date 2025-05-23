<?php

namespace Tests\App\Controller\EnMarche\VotingPlatform;

use App\DataFixtures\ORM\LoadVotingPlatformElectionData;
use App\Repository\VotingPlatform\VoteResultRepository;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;
use Tests\App\AbstractEnMarcheWebTestCase;
use Tests\App\Controller\ControllerTestTrait;

#[Group('functional')]
#[Group('voting-platform')]
class CommitteeAdherentVotingPlatformControllerTest extends AbstractEnMarcheWebTestCase
{
    use ControllerTestTrait;

    private const ELECTION_URI_1 = '/elections/'.LoadVotingPlatformElectionData::ELECTION_UUID2.'/vote';

    private const VOTER_1 = 'assesseur@en-marche-dev.fr';

    public function testAsConnectedUserICannotAccessToVotingPlatformIfIAmNotInVotersList(): void
    {
        self::authenticateAsAdherent($this->client, 'carl999@example.fr');

        $this->client->request(Request::METHOD_GET, self::ELECTION_URI_1);

        $this->assertClientIsRedirectedTo('//vox.code/', $this->client);
    }

    public function testAsAdherentVoterICanAccessToVotingPlatform(): void
    {
        self::authenticateAsAdherent($this->client, self::VOTER_1);

        $this->client->request(Request::METHOD_GET, self::ELECTION_URI_1);

        $this->isSuccessful($this->client->getResponse());
    }

    public function testAsAdherentVoterICanVote(): void
    {
        $this->client->followRedirects();

        self::authenticateAsAdherent($this->client, self::VOTER_1);

        $crawler = $this->client->request(Request::METHOD_GET, self::ELECTION_URI_1);

        $this->assertStringContainsString('Désignation du binôme d’adhérents siégeant au Conseil territorial', $crawler->filter('.introduction-header h1')->text());

        $this->assertCount(6, $crawler->filter('.candidate__box'));

        $form = $crawler->selectButton('Confirmer')->form();
        $candidates = $form['election_candidates']['poolChoice']->availableOptionValues();

        self::assertCount(6, $candidates);

        $this->client->submit($form);

        $this->assertStringContainsString('Une réponse est obligatoire', $this->client->getResponse()->getContent());

        $crawler = $this->client->submit($form, ['election_candidates' => [
            'poolChoice' => $candidates[0],
        ]]);

        $form = $crawler->selectButton('Confirmer')->form();

        self::assertCount(6, $candidates);

        $crawler = $this->client->submit($form, ['election_candidates' => [
            'poolChoice' => '-1',
        ]]);

        self::assertStringContainsString('Confirmez-vous votre choix ?', $this->client->getResponse()->getContent());

        $this->client->submit($crawler->selectButton('Confirmer mon choix')->form());

        self::assertStringContainsString('Félicitations, vos bulletins sont dans l\'urne !', $this->client->getResponse()->getContent());

        $crawler = $this->client->request(Request::METHOD_GET, self::ELECTION_URI_1);

        $results = $this->get(VoteResultRepository::class)->findAll();
        $this->assertMatchesRegularExpression('/[[:alnum:]]{3}-[[:alnum:]]{4}-[[:alnum:]]{3}/', end($results)->getVoterKey());

        $this->assertStringEndsWith('//vox.code/', $crawler->getUri());
    }
}
