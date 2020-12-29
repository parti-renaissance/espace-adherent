<?php

namespace Tests\App\Admin;

use App\Committee\CommitteeAdherentMandateManager;
use App\DataFixtures\ORM\LoadAdherentData;
use App\DataFixtures\ORM\LoadCommitteeData;
use App\Repository\CommitteeRepository;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\Controller\ControllerTestTrait;
use Tests\App\MessengerTestTrait;

/**
 * @group functional
 * @group admin
 */
class CommitteeAdminTest extends WebTestCase
{
    use ControllerTestTrait;
    use MessengerTestTrait;

    /**
     * @var CommitteeRepository
     */
    private $committeeRepository;

    /**
     * @dataProvider provideActions
     */
    public function testCannotChangeMandateIfCommitteeNotApprovedAction(string $action): void
    {
        $committee = $this->committeeRepository->findOneByUuid(LoadCommitteeData::COMMITTEE_2_UUID);
        $adherent = $this->getAdherentRepository()->findOneByUuid(LoadAdherentData::ADHERENT_1_UUID);

        $this->assertFalse($committee->isApproved());

        $this->authenticateAsAdmin($this->client);

        $this->client->request(
            Request::METHOD_GET,
            \sprintf('/admin/committee/%s/members/%s/%s-mandate', $committee->getId(), $adherent->getId(), $action)
        );
        $this->assertResponseStatusCode(Response::HTTP_BAD_REQUEST, $this->client->getResponse());
    }

    public function provideActions(): iterable
    {
        yield [CommitteeAdherentMandateManager::CREATE_ACTION];
        yield [CommitteeAdherentMandateManager::FINISH_ACTION];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->init();

        $this->committeeRepository = $this->getCommitteeRepository();
    }

    protected function tearDown(): void
    {
        $this->kill();

        $this->committeeRepository = null;

        parent::tearDown();
    }
}
