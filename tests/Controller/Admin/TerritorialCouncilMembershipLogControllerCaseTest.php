<?php

namespace Tests\App\Controller\Admin;

use App\Controller\Admin\AdminTerritorialCouncilMembershipLogController;
use App\Entity\TerritorialCouncil\TerritorialCouncilMembershipLog;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractRenaissanceWebTestCase;
use Tests\App\Controller\ControllerTestTrait;

#[Group('functional')]
#[Group('admin')]
class TerritorialCouncilMembershipLogControllerCaseTest extends AbstractRenaissanceWebTestCase
{
    use ControllerTestTrait;

    private $tcMembershipLogRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tcMembershipLogRepository = $this->getRepository(TerritorialCouncilMembershipLog::class);
    }

    protected function tearDown(): void
    {
        $this->tcMembershipLogRepository = null;

        parent::tearDown();
    }

    public function testCannotSetResolvedIfIncorrectStatus(): void
    {
        $membershipLog = $this->tcMembershipLogRepository->findOneBy(['isResolved' => false]);

        $this->authenticateAsAdmin($this->client, 'superadmin@en-marche-dev.fr');

        $this->client->request(
            Request::METHOD_GET,
            sprintf('/admin/territorial-council-membership-log/%s/%s', $membershipLog->getId(), 'incorrect_status')
        );
        $this->assertResponseStatusCode(Response::HTTP_BAD_REQUEST, $this->client->getResponse());
    }

    #[DataProvider('provideStatus')]
    public function testCannotSetResolvedIfNotValidStatus(string $status, bool $isResolved): void
    {
        $membershipLog = $this->tcMembershipLogRepository->findOneBy(['isResolved' => !$isResolved]);

        $this->authenticateAsAdmin($this->client, 'superadmin@en-marche-dev.fr');

        $this->client->request(
            Request::METHOD_GET,
            sprintf('/admin/territorial-council-membership-log/%s/%s', $membershipLog->getId(), $status)
        );
        $this->assertResponseStatusCode(Response::HTTP_BAD_REQUEST, $this->client->getResponse());
    }

    #[DataProvider('provideStatus')]
    public function testCannotSetResolvedIfNotValidToken(string $status, bool $isResolved): void
    {
        $membershipLog = $this->tcMembershipLogRepository->findOneBy(['isResolved' => $isResolved]);

        $this->authenticateAsAdmin($this->client, 'superadmin@en-marche-dev.fr');

        $this->client->request(
            Request::METHOD_GET,
            sprintf('/admin/territorial-council-membership-log/%s/%s', $membershipLog->getId(), $status)
        );
        $this->assertResponseStatusCode(Response::HTTP_BAD_REQUEST, $this->client->getResponse());
    }

    public static function provideStatus(): iterable
    {
        yield [AdminTerritorialCouncilMembershipLogController::STATUS_RESOLVED, false];
        yield [AdminTerritorialCouncilMembershipLogController::STATUS_UNRESOLVED, true];
    }
}
