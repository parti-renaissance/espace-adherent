<?php

namespace Tests\App\Controller\EnMarche\VotingPlatform;

use App\DataFixtures\ORM\LoadVotingPlatformElectionData;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group functional
 * @group voting-platform
 */
class CommitteeAdherentVotingPlatformControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    private const ELECTION_URI_1 = '/elections/'.LoadVotingPlatformElectionData::ELECTION_UUID2;

    private const VOTER_1 = 'assesseur@en-marche-dev.fr';

    public function testAsAnonymousICannotAccessToVotingPlatform(): void
    {
        $this->client->request(Request::METHOD_GET, self::ELECTION_URI_1);

        $this->assertClientIsRedirectedTo('/connexion', $this->client);
    }

    public function testAsConnectedUserICannotAccessToVotingPlatformIfIAmNotInVotersList(): void
    {
        self::authenticateAsAdherent($this->client, 'carl999@example.fr');

        $this->client->request(Request::METHOD_GET, self::ELECTION_URI_1);

        $this->assertClientIsRedirectedTo('/comites/en-marche-comite-de-evry', $this->client);
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

        $this->assertContains('Désignation du binôme d’adhérents siégeant au Conseil territorial', $crawler->filter('.introduction-header h1')->text());

        $crawler = $this->client->click($crawler->selectLink('Soumettre mon vote')->link());

        $this->assertStringEndsWith(self::ELECTION_URI_1.'/vote', $crawler->getUri());

        $this->assertCount(12, $crawler->filter('.candidate__box'));

        $form = $crawler->selectButton('Confirmer')->form();

        $candidates = $form['election_candidates']['womanCandidate']->availableOptionValues();

        self::assertCount(6, $candidates);

        $this->client->submit($form, ['election_candidates' => [
            'womanCandidate' => $candidates[0],
        ]]);

        $this->assertContains('Cette valeur ne doit pas être vide.', $this->client->getResponse()->getContent());

        $crawler = $this->client->submit($form, ['election_candidates' => [
            'womanCandidate' => $candidates[0],
            'manCandidate' => -1,
        ]]);

        self::assertContains('Confirmez-vous votre bulletin ?', $this->client->getResponse()->getContent());

        $this->client->submit($crawler->selectButton('Confirmer mon vote')->form());

        self::assertContains('A voté !', $this->client->getResponse()->getContent());

        $crawler = $this->client->request(Request::METHOD_GET, self::ELECTION_URI_1);

        $this->assertStringEndsWith('/comites/en-marche-comite-de-evry', $crawler->getUri());
    }

    protected function setUp()
    {
        parent::setUp();

        $this->init();
    }
}
