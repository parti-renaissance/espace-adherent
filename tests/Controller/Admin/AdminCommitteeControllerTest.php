<?php

namespace Tests\AppBundle\Controller\Admin;

use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\Mailer\Message\CommitteeApprovalConfirmationMessage;
use AppBundle\Mailer\Message\CommitteeApprovalReferentMessage;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\Controller\ControllerTestTrait;

/**
 * @group functional
 * @group admin
 */
class AdminCommitteeControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    private $committeeRepository;

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

    public function testApproveAction(): void
    {
        $committee = $this->committeeRepository->findOneByUuid(LoadAdherentData::COMMITTEE_2_UUID);

        $this->assertFalse($committee->isApproved());

        $this->authenticateAsAdmin($this->client);

        $this->client->request(Request::METHOD_GET, sprintf('/admin/committee/%s/approve', $committee->getId()));
        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());

        $this->get('doctrine.orm.entity_manager')->clear();

        $committee = $this->committeeRepository->findOneByUuid(LoadAdherentData::COMMITTEE_2_UUID);

        $this->assertTrue($committee->isApproved());
        $this->assertCountMails(1, CommitteeApprovalConfirmationMessage::class, 'benjyd@aol.com');
        $this->assertCountMails(1, CommitteeApprovalReferentMessage::class, 'referent@en-marche-dev.fr');
    }
}
