<?php

namespace Tests\App\Controller\Admin;

use App\Controller\Admin\AdminTerritorialCouncilMembershipLogController;
use App\Entity\TerritorialCouncil\TerritorialCouncilMembershipLog;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group functional
 * @group admin
 */
class AdminTerritorialCouncilMembershipLogControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    private $tcMembershipLogRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->init();

        $this->tcMembershipLogRepository = $this->getRepository(TerritorialCouncilMembershipLog::class);
    }

    protected function tearDown(): void
    {
        $this->kill();

        $this->tcMembershipLogRepository = null;

        parent::tearDown();
    }

    public function testCannotSetResolvedIfIncorrectStatus(): void
    {
        $membershipLog = $this->tcMembershipLogRepository->findOneBy(['isResolved' => false]);

        $this->authenticateAsAdmin($this->client, 'superadmin@en-marche-dev.fr', 'superadmin');

        $this->client->request(
            Request::METHOD_GET,
            \sprintf('/admin/territorial-council-membership-log/%s/%s', $membershipLog->getId(), 'incorrect_status')
        );
        $this->assertResponseStatusCode(Response::HTTP_BAD_REQUEST, $this->client->getResponse());
        $this->assertStringContainsString('Status &quot;incorrect_status&quot; is not authorized', $this->client->getResponse()->getContent());
    }

    /**
     * @dataProvider provideStatus
     */
    public function testCannotSetResolvedIfNotValidStatus(string $status, bool $isResolved): void
    {
        $membershipLog = $this->tcMembershipLogRepository->findOneBy(['isResolved' => !$isResolved]);

        $this->authenticateAsAdmin($this->client, 'superadmin@en-marche-dev.fr', 'superadmin');

        $this->client->request(
            Request::METHOD_GET,
            \sprintf('/admin/territorial-council-membership-log/%s/%s', $membershipLog->getId(), $status)
        );
        $this->assertResponseStatusCode(Response::HTTP_BAD_REQUEST, $this->client->getResponse());
        $this->assertStringContainsString($isResolved ? 'Ce log n&#039;est pas encore résolu.' : 'Ce log est déjà résolu.', $this->client->getResponse()->getContent());
    }

    /**
     * @dataProvider provideStatus
     */
    public function testCannotSetResolvedIfNotValidToken(string $status, bool $isResolved): void
    {
        $membershipLog = $this->tcMembershipLogRepository->findOneBy(['isResolved' => $isResolved]);

        $this->authenticateAsAdmin($this->client, 'superadmin@en-marche-dev.fr', 'superadmin');

        $this->client->request(
            Request::METHOD_GET,
            \sprintf('/admin/territorial-council-membership-log/%s/%s', $membershipLog->getId(), $status)
        );
        $this->assertResponseStatusCode(Response::HTTP_BAD_REQUEST, $this->client->getResponse());
        $this->assertStringContainsString('Invalid Csrf token provided.', $this->client->getResponse()->getContent());
    }

    public function provideStatus(): iterable
    {
        yield [AdminTerritorialCouncilMembershipLogController::STATUS_RESOLVED, false];
        yield [AdminTerritorialCouncilMembershipLogController::STATUS_UNRESOLVED, true];
    }
}
