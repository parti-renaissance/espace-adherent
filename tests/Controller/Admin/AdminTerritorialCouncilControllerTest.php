<?php

namespace Tests\App\Controller\Admin;

use App\DataFixtures\ORM\LoadAdherentData;
use App\TerritorialCouncil\PoliticalCommitteeManager;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group functional
 * @group admin
 */
class AdminTerritorialCouncilControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    private $territorialCouncilRepository;

    protected function setUp()
    {
        parent::setUp();

        $this->init();

        $this->territorialCouncilRepository = $this->getTerritorialCouncilRepository();
    }

    protected function tearDown()
    {
        $this->kill();

        $this->territorialCouncilRepository = null;

        parent::tearDown();
    }

    public function testCannotChangePoliticalCommitteeMembershipIfNotValidAction(): void
    {
        $territorialCouncil = $this->territorialCouncilRepository->findOneBy(['codes' => '75']);
        $adherent = $this->getAdherentRepository()->findOneByUuid(LoadAdherentData::ADHERENT_1_UUID);

        $this->authenticateAsAdmin($this->client);

        $this->client->request(
            Request::METHOD_GET,
            \sprintf('/admin/territorialcouncil/%s/members/%s/%s-membership', $territorialCouncil->getId(), $adherent->getId(), 'invalid_action')
        );
        $this->assertResponseStatusCode(Response::HTTP_BAD_REQUEST, $this->client->getResponse());
        $this->assertContains('Action &quot;invalid_action&quot; is not authorized', $this->client->getResponse()->getContent());
    }

    /**
     * @dataProvider provideActions
     */
    public function testCannotChangePoliticalCommitteeMembershipIfNotValidToken(string $action): void
    {
        $territorialCouncil = $this->territorialCouncilRepository->findOneBy(['codes' => '75']);
        $adherent = $this->getAdherentRepository()->findOneByUuid(LoadAdherentData::ADHERENT_1_UUID);

        $this->authenticateAsAdmin($this->client);

        $this->client->request(
            Request::METHOD_GET,
            \sprintf('/admin/territorialcouncil/%s/members/%s/%s-membership', $territorialCouncil->getId(), $adherent->getId(), $action)
        );
        $this->assertResponseStatusCode(Response::HTTP_BAD_REQUEST, $this->client->getResponse());
        $this->assertContains('Invalid Csrf token provided.', $this->client->getResponse()->getContent());
    }

    public function provideActions(): iterable
    {
        yield [PoliticalCommitteeManager::CREATE_ACTION];
        yield [PoliticalCommitteeManager::REMOVE_ACTION];
    }
}
