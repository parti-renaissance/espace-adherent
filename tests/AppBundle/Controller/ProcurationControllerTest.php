<?php

namespace Tests\AppBundle\Controller;

use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\Entity\ProcurationProxy;
use AppBundle\Entity\ProcurationRequest;
use AppBundle\Repository\ProcurationProxyRepository;
use AppBundle\Repository\ProcurationRequestRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Client;
use Tests\AppBundle\SqliteWebTestCase;

class ProcurationControllerTest extends SqliteWebTestCase
{
    use ControllerTestTrait;

    /** @var Client */
    private $client;

    /** @var ProcurationRequestRepository */
    private $procurationRequestRepostitory;

    /** @var ProcurationProxyRepository */
    private $procurationProxyRepostitory;

    /**
     * @group functionnal
     */
    public function testProcurationRequest()
    {
        // There should not be any request at the moment
        $this->assertEmpty($this->procurationRequestRepostitory->findAll());

        // Initial form
        $crawler = $this->client->request(Request::METHOD_GET, '/procuration');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->submit($crawler->filter('form[name=app_procuration_vote]')->form([
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

        $this->client->submit($crawler->filter('form[name=app_procuration_profile]')->form([
            'app_procuration_profile' => [
                'gender' => 'male',
                'firstNames' => 'Paul, Jean, Martin',
                'lastName' => 'Dupont',
                'emailAddress' => 'paul@dupont.tld',
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

        $this->client->submit($crawler->filter('form[name=app_procuration_elections]')->form([
            'g-recaptcha-response' => 'dummy',
            'app_procuration_elections' => [
                'electionPresidentialFirstRound' => true,
                'electionPresidentialSecondRound' => false,
                'electionLegislativeFirstRound' => true,
                'electionLegislativeSecondRound' => false,
                'reason' => ProcurationRequest::REASON_HEALTH,
            ],
        ]));

        // Procuration request should have been saved
        /* @var ProcurationRequest $request */
        $this->assertCount(1, $requests = $this->procurationRequestRepostitory->findAll());
        $this->assertInstanceOf(ProcurationRequest::class, $request = $requests[0]);

        $this->assertSame('FR', $request->getVoteCountry());
        $this->assertSame('92110', $request->getVotePostalCode());
        $this->assertSame('Clichy', $request->getVoteCityName());
        $this->assertSame('TestOfficeName', $request->getVoteOffice());
        $this->assertSame('male', $request->getGender());
        $this->assertSame('Paul, Jean, Martin', $request->getFirstNames());
        $this->assertSame('Dupont', $request->getLastName());
        $this->assertSame('paul@dupont.tld', $request->getEmailAddress());
        $this->assertSame('FR', $request->getCountry());
        $this->assertSame('69001', $request->getPostalCode());
        $this->assertSame('Lyon 1er', $request->getCityName());
        $this->assertSame('6 rue Neyret', $request->getAddress());
        $this->assertTrue($request->getElectionPresidentialFirstRound());
        $this->assertFalse($request->getElectionPresidentialSecondRound());
        $this->assertTrue($request->getElectionLegislativeFirstRound());
        $this->assertFalse($request->getElectionLegislativeSecondRound());
        $this->assertSame(ProcurationRequest::REASON_HEALTH, $request->getReason());

        // Redirected to thanks
        $this->assertClientIsRedirectedTo('/procuration/je-demande/merci', $this->client);
        $this->client->followRedirect();
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
    }

    /**
     * @group functionnal
     */
    public function testProcurationProposalWithoutValidReferentThrows404()
    {
        $this->client->request(Request::METHOD_GET, '/procuration/je-propose');
        $this->assertResponseStatusCode(Response::HTTP_NOT_FOUND, $this->client->getResponse());

        $this->client->request(Request::METHOD_GET, '/procuration/je-propose?uuid='.LoadAdherentData::ADHERENT_1_UUID);
        $this->assertResponseStatusCode(Response::HTTP_NOT_FOUND, $this->client->getResponse());
    }

    /**
     * @group functionnal
     */
    public function testProcurationProposal()
    {
        // There should not be any proposal at the moment
        $this->assertEmpty($this->procurationProxyRepostitory->findAll());

        // Initial form
        $crawler = $this->client->request(Request::METHOD_GET, '/procuration/je-propose?uuid='.LoadAdherentData::ADHERENT_8_UUID);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->submit($crawler->filter('form[name=app_procuration_proposal]')->form([
            'g-recaptcha-response' => 'dummy',
            'app_procuration_proposal' => [
                'gender' => 'male',
                'firstNames' => 'Paul, Jean, Martin',
                'lastName' => 'Dupont',
                'emailAddress' => 'paul@dupont.tld',
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
                'electionPresidentialFirstRound' => true,
                'electionPresidentialSecondRound' => false,
                'electionLegislativeFirstRound' => true,
                'electionLegislativeSecondRound' => false,
                'conditions' => true,
            ],
        ]));

        // Procuration request should have been saved
        /* @var ProcurationProxy $proposal */
        $this->assertCount(1, $proposals = $this->procurationProxyRepostitory->findAll());
        $this->assertInstanceOf(ProcurationProxy::class, $proposal = $proposals[0]);

        $this->assertSame('FR', $proposal->getVoteCountry());
        $this->assertSame('92110', $proposal->getVotePostalCode());
        $this->assertSame('Clichy', $proposal->getVoteCityName());
        $this->assertSame('TestOfficeName', $proposal->getVoteOffice());
        $this->assertSame('male', $proposal->getGender());
        $this->assertSame('Paul, Jean, Martin', $proposal->getFirstNames());
        $this->assertSame('Dupont', $proposal->getLastName());
        $this->assertSame('paul@dupont.tld', $proposal->getEmailAddress());
        $this->assertSame('FR', $proposal->getCountry());
        $this->assertSame('69001', $proposal->getPostalCode());
        $this->assertSame('Lyon 1er', $proposal->getCityName());
        $this->assertSame('6 rue Neyret', $proposal->getAddress());
        $this->assertTrue($proposal->getElectionPresidentialFirstRound());
        $this->assertFalse($proposal->getElectionPresidentialSecondRound());
        $this->assertTrue($proposal->getElectionLegislativeFirstRound());
        $this->assertFalse($proposal->getElectionLegislativeSecondRound());

        // Redirected to thanks
        $this->assertClientIsRedirectedTo('/procuration/je-propose/merci?uuid='.LoadAdherentData::ADHERENT_8_UUID, $this->client);
        $this->client->followRedirect();
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
    }

    // myRequestAction is tested in ProcurationManagerControllerTest

    protected function setUp()
    {
        parent::setUp();

        $this->init([
            LoadAdherentData::class,
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
