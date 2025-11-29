<?php

declare(strict_types=1);

namespace Tests\App\Controller\Admin;

use App\DataFixtures\ORM\LoadCommitteeV1Data;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractAdminWebTestCase;
use Tests\App\Controller\ControllerTestTrait;
use Tests\App\MessengerTestTrait;

#[Group('functional')]
#[Group('admin')]
class CommitteeCRUDControllerCaseTest extends AbstractAdminWebTestCase
{
    use ControllerTestTrait;
    use MessengerTestTrait;

    private $committeeRepository;

    public function testApproveCommitteeAction(): void
    {
        $committee = $this->committeeRepository->findOneByUuid(LoadCommitteeV1Data::COMMITTEE_2_UUID);

        $this->assertFalse($committee->isApproved());

        $this->authenticateAsAdmin($this->client);

        $crawler = $this->client->request(Request::METHOD_GET, \sprintf('/app/committee/%s/approve', $committee->getId()));

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $crawler = $this->client->submit($crawler->selectButton('Suivant')->form());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->submit($crawler->selectButton('Confirmer')->form());

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());

        $this->client->followRedirect();

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->get('doctrine.orm.entity_manager')->clear();

        $committee = $this->committeeRepository->findOneByUuid(LoadCommitteeV1Data::COMMITTEE_2_UUID);

        $this->assertTrue($committee->isApproved());
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->committeeRepository = $this->getCommitteeRepository();
    }

    protected function tearDown(): void
    {
        $this->committeeRepository = null;

        parent::tearDown();
    }
}
