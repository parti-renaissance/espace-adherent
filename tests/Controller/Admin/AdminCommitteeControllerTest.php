<?php

namespace Tests\AppBundle\Controller\EnMarche;

use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\DataFixtures\ORM\LoadAdminData;
use AppBundle\Mailjet\Message\CommitteeApprovalConfirmationMessage;
use AppBundle\Mailjet\Message\CommitteeApprovalReferentMessage;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\Controller\ControllerTestTrait;
use Tests\AppBundle\MysqlWebTestCase;

/**
 * @group functional
 */
class AdminCommitteeControllerTest extends MysqlWebTestCase
{
    use ControllerTestTrait;

    private $committeeRepository;

    public function testApproveAction()
    {
        $committee = $this->committeeRepository->findOneByUuid(LoadAdherentData::COMMITTEE_2_UUID);

        $crawler = $this->client->request(Request::METHOD_GET, '/admin/login');

        // connect as admin
        $this->client->submit($crawler->selectButton('Je me connecte')->form([
            '_admin_email' => 'admin@en-marche-dev.fr',
            '_admin_password' => 'admin',
        ]));

        $this->client->request(Request::METHOD_GET, sprintf('/admin/committee/%s/approve', $committee->getId()));
        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());

        $this->get('doctrine.orm.entity_manager')->clear();

        $committee = $this->committeeRepository->findOneByUuid(LoadAdherentData::COMMITTEE_2_UUID);

        $this->assertTrue($committee->isApproved());
        $this->assertCount(1, $this->getMailjetEmailRepository()->findRecipientMessages(CommitteeApprovalConfirmationMessage::class, 'benjyd@aol.com'));
        $this->assertCount(1, $this->getMailjetEmailRepository()->findRecipientMessages(CommitteeApprovalReferentMessage::class, 'referent@en-marche-dev.fr'));
    }

    protected function setUp()
    {
        parent::setUp();

        $this->init([
            LoadAdminData::class,
            LoadAdherentData::class,
        ]);

        $this->committeeRepository = $this->getCommitteeRepository();
        $this->emailRepository = $this->getMailjetEmailRepository();
    }

    protected function tearDown()
    {
        $this->kill();

        $this->committeeRepository = null;
        $this->emailRepository = null;

        parent::tearDown();
    }
}
