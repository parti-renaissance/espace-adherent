<?php

declare(strict_types=1);

namespace Tests\App\Controller\Api\NationalEvent;

use App\DataFixtures\ORM\LoadAdherentData;
use App\DataFixtures\ORM\LoadClientData;
use App\Entity\NationalEvent\EventInscription;
use App\NationalEvent\InscriptionStatusEnum;
use App\OAuth\Model\GrantTypeEnum;
use App\OAuth\Model\Scope;
use App\Repository\NationalEvent\EventInscriptionRepository;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Uuid;
use Tests\App\AbstractApiTestCase;
use Tests\App\Controller\ApiControllerTestTrait;
use Tests\App\Controller\ControllerTestTrait;

#[Group('functional')]
#[Group('api')]
class ScanTicketControllerTest extends AbstractApiTestCase
{
    use ControllerTestTrait;
    use ApiControllerTestTrait;

    private const SCANNER_EMAIL = 'president-ad@renaissance-dev.fr';

    private ?EventInscriptionRepository $eventInscriptionRepository = null;

    public function testScanApprovedTicketRecordsScanAndReturnsValidStatus(): void
    {
        $ticketUuid = $this->findInscriptionByStatus(InscriptionStatusEnum::ACCEPTED)->ticketUuid;

        $this->scan($ticketUuid->toRfc4122());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        self::assertSame('valid', $this->decodeResponse()['status']['code']);

        $inscription = $this->refetch($ticketUuid);
        self::assertCount(1, $inscription->getTicketScans());
        self::assertNotNull($inscription->lastTicketScannedAt);
        self::assertSame(self::SCANNER_EMAIL, $inscription->getTicketScans()[0]->scannedBy?->getEmailAddress());
    }

    public function testRescanWithinOneMinuteDoesNotCreateDuplicateScan(): void
    {
        $ticketUuid = $this->findInscriptionByStatus(InscriptionStatusEnum::ACCEPTED)->ticketUuid;

        $this->scan($ticketUuid->toRfc4122());
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->scan($ticketUuid->toRfc4122());
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        // The `-1 minute` guard, made atomic by the pessimistic lock, must keep a single scan.
        self::assertCount(1, $this->refetch($ticketUuid)->getTicketScans());
    }

    public function testScanNonApprovedTicketRecordsScanButReturnsInvalidStatus(): void
    {
        $ticketUuid = $this->findInscriptionByStatus(InscriptionStatusEnum::WAITING_PAYMENT)->ticketUuid;

        $this->scan($ticketUuid->toRfc4122());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        self::assertSame('invalid', $this->decodeResponse()['status']['code']);

        // The scan is still logged for audit even when entry is refused.
        self::assertCount(1, $this->refetch($ticketUuid)->getTicketScans());
    }

    public function testScanUnknownTicketReturnsUnknownStatus(): void
    {
        $this->scan(Uuid::v4()->toRfc4122());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        self::assertSame('unknown', $this->decodeResponse()['status']['code']);
    }

    public function testScanRequiresAuthentication(): void
    {
        $ticketUuid = $this->findInscriptionByStatus(InscriptionStatusEnum::ACCEPTED)->ticketUuid;

        $this->client->request(Request::METHOD_POST, $this->scanUrl($ticketUuid->toRfc4122()));

        $this->assertResponseStatusCode(Response::HTTP_UNAUTHORIZED, $this->client->getResponse());
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->eventInscriptionRepository = $this->get(EventInscriptionRepository::class);
    }

    protected function tearDown(): void
    {
        $this->eventInscriptionRepository = null;

        parent::tearDown();
    }

    private function scan(string $ticketUuid): void
    {
        $accessToken = $this->getAccessToken(
            LoadClientData::CLIENT_10_UUID,
            'MWFod6bOZb2mY3wLE=4THZGbOfHJvRHk8bHdtZP3BTr',
            GrantTypeEnum::PASSWORD,
            Scope::JEMARCHE_APP,
            self::SCANNER_EMAIL,
            LoadAdherentData::DEFAULT_PASSWORD,
        );

        $this->client->request(Request::METHOD_POST, $this->scanUrl($ticketUuid), [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => "Bearer $accessToken",
        ]);
    }

    private function scanUrl(string $ticketUuid): string
    {
        return \sprintf('/api/v3/national_event_inscriptions/%s/scan', $ticketUuid);
    }

    private function findInscriptionByStatus(string $status): EventInscription
    {
        $inscription = $this->eventInscriptionRepository->findOneBy(['status' => $status]);
        self::assertInstanceOf(EventInscription::class, $inscription);
        self::assertCount(0, $inscription->getTicketScans());

        return $inscription;
    }

    private function refetch(Uuid $ticketUuid): EventInscription
    {
        $this->getEntityManager(EventInscription::class)->clear();
        $inscription = $this->eventInscriptionRepository->findOneBy(['ticketUuid' => $ticketUuid]);
        self::assertInstanceOf(EventInscription::class, $inscription);

        return $inscription;
    }

    private function decodeResponse(): array
    {
        return json_decode($this->client->getResponse()->getContent(), true, 512, \JSON_THROW_ON_ERROR);
    }
}
