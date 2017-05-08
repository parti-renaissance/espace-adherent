<?php

namespace Tests\AppBundle\Controller;

use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\DataFixtures\ORM\LoadProcurationData;
use AppBundle\Entity\ProcurationProxy;
use AppBundle\Entity\ProcurationRequest;
use AppBundle\Repository\ProcurationProxyRepository;
use AppBundle\Repository\ProcurationRequestRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Client;
use Tests\AppBundle\SqliteWebTestCase;

/**
 * @group functionnal
 */
class ProcurationControllerTest extends SqliteWebTestCase
{
    use ControllerTestTrait;

    /** @var Client */
    private $client;

    /** @var ProcurationRequestRepository */
    private $procurationRequestRepostitory;

    /** @var ProcurationProxyRepository */
    private $procurationProxyRepostitory;

    public function testProcurationRequest()
    {
        return;

        $this->assertCount(5, $this->procurationRequestRepostitory->findAll());

        // Initial form
        $crawler = $this->client->request(Request::METHOD_GET, '/procuration/je-demande');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->submit($crawler->selectButton('Je continue')->form([
            'app_procuration_vote' => [
                'voteCountry' => 'FR',
                'votePostalCode' => '92110',
                'voteCity' => '92110-92024',
                'voteCityName' => '',
                'voteOffice' => 'TestOfficeName',
            ],
        ]));

        $this->assertClientIsRedirectedTo('/procuration/je-demande/mes-coordonnees', $this->client);
        $crawler = $this->client->followRedirect();

        // Profile
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $crawler = $this->client->submit($crawler->selectButton('Je continue')->form([
            'app_procuration_profile' => [
                'gender' => 'male',
                'firstNames' => 'Paul, Jean, Martin',
                'lastName' => 'Dupont',
                'emailAddress' => 'timothe.baume@example.gb',
                'address' => '6 rue Neyret',
                'country' => 'FR',
                'postalCode' => '69001',
                'city' => '69001-69381',
                'cityName' => '',
                'phone' => [
                    'country' => 'FR',
                    'number' => '',
                ],
                'birthdate' => [
                    'year' => '1950',
                    'month' => '1',
                    'day' => '20',
                ],
            ],
        ]));

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame('Le numéro de téléphone est obligatoire.', $crawler->filter('.form__error')->text());
        $this->assertSame(0, $crawler->filter('.form--warning')->count());

        $this->client->submit($crawler->selectButton('Je continue')->form([
            'app_procuration_profile' => [
                'gender' => 'male',
                'firstNames' => 'Paul, Jean, Martin',
                'lastName' => 'Dupont',
                'emailAddress' => 'timothe.baume@example.gb',
                'address' => '6 rue Neyret',
                'country' => 'FR',
                'postalCode' => '69001',
                'city' => '69001-69381',
                'cityName' => '',
                'phone' => [
                    'country' => 'FR',
                    'number' => '0140998080',
                ],
                'birthdate' => [
                    'year' => '1950',
                    'month' => '1',
                    'day' => '20',
                ],
            ],
        ]));

        $this->assertClientIsRedirectedTo('/procuration/je-demande/ma-procuration', $this->client);
        $crawler = $this->client->followRedirect();

        // Elections
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->submit($crawler->selectButton('Je continue')->form([
            'g-recaptcha-response' => 'dummy',
            'app_procuration_elections' => [
                'electionLegislativeFirstRound' => true,
                'electionLegislativeSecondRound' => false,
                'reason' => ProcurationRequest::REASON_HEALTH,
                'authorization' => true,
            ],
        ]));

        // Procuration request should have been saved
        /* @var ProcurationRequest $request */
        $this->assertCount(6, $requests = $this->procurationRequestRepostitory->findAll());
        $this->assertInstanceOf(ProcurationRequest::class, $request = end($requests));

        $this->assertSame('FR', $request->getVoteCountry());
        $this->assertSame('92110', $request->getVotePostalCode());
        $this->assertSame('Clichy', $request->getVoteCityName());
        $this->assertSame('TestOfficeName', $request->getVoteOffice());
        $this->assertSame('male', $request->getGender());
        $this->assertSame('Paul, Jean, Martin', $request->getFirstNames());
        $this->assertSame('Dupont', $request->getLastName());
        $this->assertSame('timothe.baume@example.gb', $request->getEmailAddress());
        $this->assertSame('FR', $request->getCountry());
        $this->assertSame('69001', $request->getPostalCode());
        $this->assertSame('Lyon 1er', $request->getCityName());
        $this->assertSame('6 rue Neyret', $request->getAddress());
        $this->assertFalse($request->getElectionPresidentialFirstRound());
        $this->assertFalse($request->getElectionPresidentialSecondRound());
        $this->assertTrue($request->getElectionLegislativeFirstRound());
        $this->assertFalse($request->getElectionLegislativeSecondRound());
        $this->assertSame(ProcurationRequest::REASON_HEALTH, $request->getReason());

        // Redirected to thanks
        $this->assertClientIsRedirectedTo('/procuration/je-demande/merci', $this->client);
        $this->client->followRedirect();
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
    }

    public function testProcurationProposal()
    {
        // There should not be any proposal at the moment
        $this->assertCount(3, $this->procurationProxyRepostitory->findAll());

        // Initial form
        $crawler = $this->client->request(Request::METHOD_GET, '/procuration/je-propose?uuid='.LoadAdherentData::ADHERENT_8_UUID);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $crawler = $this->client->submit($crawler->selectButton('Je continue')->form([
            'g-recaptcha-response' => 'dummy',
            'app_procuration_proposal' => [
                'gender' => 'male',
                'firstNames' => 'Paul, Jean, Martin',
                'lastName' => 'Dupont',
                'emailAddress' => 'maxime.michaux@example.fr',
                'address' => '6 rue Neyret',
                'country' => 'FR',
                'postalCode' => '69001',
                'city' => '69001-69381',
                'cityName' => '',
                'phone' => [
                    'country' => 'FR',
                    'number' => '',
                ],
                'birthdate' => [
                    'year' => '1950',
                    'month' => '1',
                    'day' => '20',
                ],
                'voteCountry' => 'FR',
                'votePostalCode' => '92110',
                'voteCity' => '92110-92024',
                'voteCityName' => '',
                'voteOffice' => 'TestOfficeName',
                'electionLegislativeFirstRound' => true,
                'electionLegislativeSecondRound' => false,
                'conditions' => true,
                'authorization' => true,
            ],
        ]));

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame('Le numéro de téléphone est obligatoire.', $crawler->filter('.form__error')->text());
        $this->assertSame(0, $crawler->filter('.form--warning')->count());

        $this->client->submit($crawler->selectButton('Je continue')->form([
            'g-recaptcha-response' => 'dummy',
            'app_procuration_proposal' => [
                'gender' => 'male',
                'firstNames' => 'Paul, Jean, Martin',
                'lastName' => 'Dupont',
                'emailAddress' => 'maxime.michaux@example.fr',
                'address' => '6 rue Neyret',
                'country' => 'FR',
                'postalCode' => '69001',
                'city' => '69001-69381',
                'cityName' => '',
                'phone' => [
                    'country' => 'FR',
                    'number' => '0140998080',
                ],
                'birthdate' => [
                    'year' => '1950',
                    'month' => '1',
                    'day' => '20',
                ],
                'voteCountry' => 'FR',
                'votePostalCode' => '92110',
                'voteCity' => '92110-92024',
                'voteCityName' => '',
                'voteOffice' => 'TestOfficeName',
                'electionLegislativeFirstRound' => true,
                'electionLegislativeSecondRound' => false,
                'conditions' => true,
            ],
        ]));

        // Procuration request should have been saved
        /* @var ProcurationProxy $proposal */
        $this->assertCount(4, $proposals = $this->procurationProxyRepostitory->findAll());
        $this->assertInstanceOf(ProcurationProxy::class, $proposal = end($proposals));

        $this->assertSame('FR', $proposal->getVoteCountry());
        $this->assertSame('92110', $proposal->getVotePostalCode());
        $this->assertSame('Clichy', $proposal->getVoteCityName());
        $this->assertSame('TestOfficeName', $proposal->getVoteOffice());
        $this->assertSame('male', $proposal->getGender());
        $this->assertSame('Paul, Jean, Martin', $proposal->getFirstNames());
        $this->assertSame('Dupont', $proposal->getLastName());
        $this->assertSame('maxime.michaux@example.fr', $proposal->getEmailAddress());
        $this->assertSame('FR', $proposal->getCountry());
        $this->assertSame('69001', $proposal->getPostalCode());
        $this->assertSame('Lyon 1er', $proposal->getCityName());
        $this->assertSame('6 rue Neyret', $proposal->getAddress());
        $this->assertFalse($proposal->getElectionPresidentialFirstRound());
        $this->assertFalse($proposal->getElectionPresidentialSecondRound());
        $this->assertTrue($proposal->getElectionLegislativeFirstRound());
        $this->assertFalse($proposal->getElectionLegislativeSecondRound());

        // Redirected to thanks
        $this->assertClientIsRedirectedTo('/procuration/je-propose/merci?uuid='.LoadAdherentData::ADHERENT_8_UUID, $this->client);
        $this->client->followRedirect();
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
    }

    public function testProcurationRequestUniqueEmailBirthdate()
    {
        return;

        $this->assertCount(5, $this->procurationRequestRepostitory->findAll());

        // Initial form
        $crawler = $this->client->request(Request::METHOD_GET, '/procuration/je-demande');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->submit($crawler->filter('form[name=app_procuration_vote]')->form([
            'app_procuration_vote' => [
                'voteCountry' => 'FR',
                'votePostalCode' => '75018',
                'voteCity' => '75018-75118',
                'voteCityName' => '',
                'voteOffice' => 'TestOfficeName',
            ],
        ]));

        $this->assertClientIsRedirectedTo('/procuration/je-demande/mes-coordonnees', $this->client);
        $crawler = $this->client->followRedirect();

        // Profile
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $crawler = $this->client->submit($crawler->selectButton('Je continue')->form([
            'app_procuration_profile' => [
                'gender' => 'female',
                'firstNames' => 'Carine, Margaux',
                'lastName' => 'Édouard',
                'emailAddress' => 'caroline.edouard@example.fr',
                'address' => '165 rue Marcadet',
                'country' => 'FR',
                'postalCode' => '75018',
                'city' => '75018-75118',
                'cityName' => '',
                'phone' => [
                    'country' => 'FR',
                    'number' => '0600010203',
                ],
                'birthdate' => [
                    'year' => '1968',
                    'month' => '10',
                    'day' => '9',
                ],
            ],
        ]));

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertContains('Vous êtes déjà inscrit comme mandant.', $crawler->filter('.procuration__banner__form')->text());
    }

    public function testProcurationProposalUniqueEmailBirthdate()
    {
        // There should not be any proposal at the moment
        $this->assertCount(3, $this->procurationProxyRepostitory->findAll());

        // Initial form
        $crawler = $this->client->request(Request::METHOD_GET, '/procuration/je-propose?uuid='.LoadAdherentData::ADHERENT_8_UUID);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $crawler = $this->client->submit($crawler->selectButton('Je continue')->form([
            'g-recaptcha-response' => 'dummy',
            'app_procuration_proposal' => [
                'gender' => 'male',
                'firstNames' => 'Maxime',
                'lastName' => 'Michaux',
                'emailAddress' => 'maxime.michaux@example.fr',
                'address' => '14 rue Jules Ferry',
                'country' => 'FR',
                'postalCode' => '75018',
                'city' => '75018-75120',
                'cityName' => '',
                'phone' => [
                    'country' => 'FR',
                    'number' => '0140998080',
                ],
                'birthdate' => [
                    'year' => '1989',
                    'month' => '2',
                    'day' => '17',
                ],
                'voteCountry' => 'FR',
                'votePostalCode' => '75018',
                'voteCity' => '75018-75120',
                'voteCityName' => '',
                'voteOffice' => 'Mairie',
                'electionLegislativeFirstRound' => true,
                'electionLegislativeSecondRound' => false,
                'conditions' => true,
                'authorization' => true,
            ],
        ]));

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertContains('Vous êtes déjà inscrit comme mandataire.', $crawler->filter('.procuration__banner__form')->text());
    }

    public function testProcurationProposalManagerUuid()
    {
        $this->client->request(Request::METHOD_GET, '/procuration/je-propose?uuid='.LoadAdherentData::ADHERENT_4_UUID);
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
    }

    protected function setUp()
    {
        parent::setUp();

        $this->init([
            LoadAdherentData::class,
            LoadProcurationData::class,
        ]);

        $this->procurationRequestRepostitory = $this->getProcurationRequestRepository();
        $this->procurationProxyRepostitory = $this->getProcurationProxyRepository();
    }

    protected function tearDown()
    {
        $this->kill();

        $this->procurationRequestRepostitory = null;
        $this->procurationProxyRepostitory = null;

        parent::tearDown();
    }
}
