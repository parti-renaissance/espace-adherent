<?php

declare(strict_types=1);

namespace Tests\App\Controller\Api;

use App\DataFixtures\ORM\LoadAdherentData;
use App\DataFixtures\ORM\LoadAdherentMessageData;
use App\DataFixtures\ORM\LoadClientData;
use App\DataFixtures\ORM\LoadEventData;
use App\JeMengage\Hit\Stats\AggregatorInterface;
use App\JeMengage\Hit\TargetTypeEnum;
use App\OAuth\Model\GrantTypeEnum;
use App\OAuth\Model\Scope;
use PHPUnit\Framework\Attributes\Group;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractApiTestCase;
use Tests\App\Controller\ApiControllerTestTrait;
use Tests\App\Controller\ControllerTestTrait;
use Tests\App\Test\Helper\PHPUnitHelper;

#[Group('functional')]
#[Group('api')]
class AppHitControllerTest extends AbstractApiTestCase
{
    use ControllerTestTrait;
    use ApiControllerTestTrait;
    private const URL = '/api/v3/hit';
    private const SESSION_UUID = '5c9d9b61-da9e-4fbc-81d1-3e471b50b2d0';

    private ?string $accessToken = null;
    private AggregatorInterface $aggregator;

    public function testEventHits(): void
    {
        $eventUuid = Uuid::fromString(LoadEventData::EVENT_8_UUID);
        $date = new \DateTime()->format(\DATE_ATOM);
        $sessionUuid = self::SESSION_UUID;

        $stats = $this->aggregator->getStats(TargetTypeEnum::Event, $eventUuid);

        self::assertSame([
            'unique_clicks' => [
                'app' => 10,
                'app_rate' => 100.0,
                'email' => 0,
                'email_rate' => 0.0,
                'total' => 10,
                'total_rate' => 100.0,
            ],
            'unique_impressions' => [
                'list' => 10,
                'timeline' => 0,
                'total' => 10,
            ],
            'unique_opens' => [
                'app' => 10,
                'direct_link' => 10,
                'email' => 0,
                'list' => 0,
                'notification' => 0,
                'timeline' => 0,
                'total' => 10,
                'total_rate' => 100.0,
            ],
        ], $stats->jsonSerialize());

        // Send new impression hit
        $this->client->request(Request::METHOD_POST, self::URL, [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => "Bearer $this->accessToken",
        ], <<<JSON
            {
              "event_type": "impression",
              "activity_session_uuid": "$sessionUuid",
              "app_date": "$date",
              "app_version": "5.19.0#1",
              "app_system": "web",
              "user_agent": "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:144.0) Gecko/20100101 Firefox/144.0",
              "object_type": "event",
              "object_id": "$eventUuid",
              "source":"page_timeline"
            }
            JSON
        );

        $this->assertResponseStatusCode(Response::HTTP_ACCEPTED, $this->client->getResponse());

        $stats = $this->aggregator->getStats(TargetTypeEnum::Event, $eventUuid);

        self::assertSame([
            'unique_clicks' => [
                'app' => 10,
                'app_rate' => 100.0,
                'email' => 0,
                'email_rate' => 0.0,
                'total' => 10,
                'total_rate' => 100.0,
            ],
            'unique_impressions' => [
                'list' => 10,
                'timeline' => 1,
                'total' => 11,
            ],
            'unique_opens' => [
                'app' => 10,
                'direct_link' => 10,
                'email' => 0,
                'list' => 0,
                'notification' => 0,
                'timeline' => 0,
                'total' => 10,
                'total_rate' => 90.91,
            ],
        ], $stats->jsonSerialize());

        // Send new open hit
        $this->client->request(Request::METHOD_POST, self::URL, [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => "Bearer $this->accessToken",
        ], <<<JSON
            {
              "event_type": "open",
              "activity_session_uuid": "$sessionUuid",
              "app_date": "$date",
              "app_version": "5.19.0#1",
              "app_system": "web",
              "user_agent": "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:144.0) Gecko/20100101 Firefox/144.0",
              "object_type": "event",
              "object_id": "$eventUuid",
              "source":"page_timeline"
            }
            JSON
        );

        $this->assertResponseStatusCode(Response::HTTP_ACCEPTED, $this->client->getResponse());

        $stats = $this->aggregator->getStats(TargetTypeEnum::Event, $eventUuid);

        self::assertSame([
            'unique_clicks' => [
                'app' => 10,
                'app_rate' => 90.91,
                'email' => 0,
                'email_rate' => 0.0,
                'total' => 10,
                'total_rate' => 90.91,
            ],
            'unique_impressions' => [
                'list' => 10,
                'timeline' => 1,
                'total' => 11,
            ],
            'unique_opens' => [
                'app' => 11,
                'direct_link' => 10,
                'email' => 0,
                'list' => 0,
                'notification' => 0,
                'timeline' => 1,
                'total' => 11,
                'total_rate' => 100.0,
            ],
        ], $stats->jsonSerialize());

        // Send new click hit
        $this->client->request(Request::METHOD_POST, self::URL, [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => "Bearer $this->accessToken",
        ], <<<JSON
            {
              "event_type": "click",
              "activity_session_uuid": "$sessionUuid",
              "app_date": "$date",
              "app_version": "5.19.0#1",
              "app_system": "web",
              "user_agent": "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:144.0) Gecko/20100101 Firefox/144.0",
              "object_type": "event",
              "object_id": "$eventUuid",
              "target_url": "https://parti-renaissance.fr"
            }
            JSON
        );

        $this->assertResponseStatusCode(Response::HTTP_ACCEPTED, $this->client->getResponse());

        $stats = $this->aggregator->getStats(TargetTypeEnum::Event, $eventUuid);

        self::assertSame([
            'unique_clicks' => [
                'app' => 11,
                'app_rate' => 100.0,
                'email' => 0,
                'email_rate' => 0.0,
                'total' => 11,
                'total_rate' => 100.0,
            ],
            'unique_impressions' => [
                'list' => 10,
                'timeline' => 1,
                'total' => 11,
            ],
            'unique_opens' => [
                'app' => 11,
                'direct_link' => 10,
                'email' => 0,
                'list' => 0,
                'notification' => 0,
                'timeline' => 1,
                'total' => 11,
                'total_rate' => 100.0,
            ],
        ], $stats->jsonSerialize());
    }

    public function testPublicationHits(): void
    {
        $publicationUuid = Uuid::fromString(LoadAdherentMessageData::MESSAGE_03_UUID);
        $date = new \DateTime()->format(\DATE_ATOM);
        $sessionUuid = self::SESSION_UUID;

        $stats = $this->aggregator->getStats(TargetTypeEnum::Publication, $publicationUuid);

        PHPUnitHelper::assertArraySubset([
            'contacts' => 0,
            'notifications' => [
                'android' => 0,
                'ios' => 0,
                'web' => 0,
            ],
            'unique_clicks' => [
                'app' => 0,
                'app_rate' => 0.0,
                'email' => 0,
                'email_rate' => 0.0,
                'total' => 0,
                'total_rate' => 0.0,
            ],
            'unique_emails' => null,
            'unique_impressions' => [
                'list' => 0,
                'timeline' => 0,
                'total' => 0,
            ],
            'unique_notifications' => 0,
            'unique_opens' => [
                'app' => 0,
                'app_rate' => 0.0,
                'direct_link' => 0,
                'email' => 0,
                'email_rate' => 0.0,
                'list' => 0,
                'notification' => 0,
                'notification_rate' => 0.0,
                'timeline' => 0,
                'total' => 0,
                'total_rate' => 0.0,
            ],
            'unsubscribed' => [
                'total' => 0,
                'total_rate' => 0.0,
            ],
            'visible_count' => 4,
        ], $stats->jsonSerialize());

        // Send new impression hit
        $this->client->request(Request::METHOD_POST, self::URL, [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => "Bearer $this->accessToken",
        ], <<<JSON
            {
              "event_type": "impression",
              "activity_session_uuid": "$sessionUuid",
              "app_date": "$date",
              "app_version": "5.19.0#1",
              "app_system": "web",
              "user_agent": "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:144.0) Gecko/20100101 Firefox/144.0",
              "object_type": "publication",
              "object_id": "$publicationUuid",
              "source":"page_timeline"
            }
            JSON
        );

        $this->assertResponseStatusCode(Response::HTTP_ACCEPTED, $this->client->getResponse());

        $stats = $this->aggregator->getStats(TargetTypeEnum::Publication, $publicationUuid);

        PHPUnitHelper::assertArraySubset([
            'contacts' => 0,
            'notifications' => [
                'android' => 0,
                'ios' => 0,
                'web' => 0,
            ],
            'unique_clicks' => [
                'app' => 0,
                'app_rate' => 0.0,
                'email' => 0,
                'email_rate' => 0.0,
                'total' => 0,
                'total_rate' => 0.0,
            ],
            'unique_emails' => null,
            'unique_impressions' => [
                'list' => 0,
                'timeline' => 1,
                'total' => 1,
            ],
            'unique_notifications' => 0,
            'unique_opens' => [
                'app' => 0,
                'app_rate' => 0.0,
                'direct_link' => 0,
                'email' => 0,
                'email_rate' => 0.0,
                'list' => 0,
                'notification' => 0,
                'notification_rate' => 0.0,
                'timeline' => 0,
                'total' => 0,
                'total_rate' => 0.0,
            ],
            'unsubscribed' => [
                'total' => 0,
                'total_rate' => 0.0,
            ],
            'visible_count' => 4,
        ], $stats->jsonSerialize());

        // Send new open hit
        $this->client->request(Request::METHOD_POST, self::URL, [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => "Bearer $this->accessToken",
        ], <<<JSON
            {
              "event_type": "open",
              "activity_session_uuid": "$sessionUuid",
              "app_date": "$date",
              "app_version": "5.19.0#1",
              "app_system": "web",
              "user_agent": "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:144.0) Gecko/20100101 Firefox/144.0",
              "object_type": "publication",
              "object_id": "$publicationUuid",
              "source":"page_timeline"
            }
            JSON
        );

        $this->assertResponseStatusCode(Response::HTTP_ACCEPTED, $this->client->getResponse());

        $stats = $this->aggregator->getStats(TargetTypeEnum::Publication, $publicationUuid);

        PHPUnitHelper::assertArraySubset([
            'contacts' => 0,
            'notifications' => [
                'android' => 0,
                'ios' => 0,
                'web' => 0,
            ],
            'unique_clicks' => [
                'app' => 0,
                'app_rate' => 0.0,
                'email' => 0,
                'email_rate' => 0.0,
                'total' => 0,
                'total_rate' => 0.0,
            ],
            'unique_emails' => null,
            'unique_impressions' => [
                'list' => 0,
                'timeline' => 1,
                'total' => 1,
            ],
            'unique_notifications' => 0,
            'unique_opens' => [
                'app' => 1,
                'app_rate' => 100.0,
                'direct_link' => 0,
                'email' => 0,
                'email_rate' => 0.0,
                'list' => 0,
                'notification' => 0,
                'notification_rate' => 0.0,
                'timeline' => 1,
                'total' => 1,
                'total_rate' => 100.0,
            ],
            'unsubscribed' => [
                'total' => 0,
                'total_rate' => 0.0,
            ],
            'visible_count' => 4,
        ], $stats->jsonSerialize());

        // Send new click hit
        $this->client->request(Request::METHOD_POST, self::URL, [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => "Bearer $this->accessToken",
        ], <<<JSON
            {
              "event_type": "click",
              "activity_session_uuid": "$sessionUuid",
              "app_date": "$date",
              "app_version": "5.19.0#1",
              "app_system": "web",
              "user_agent": "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:144.0) Gecko/20100101 Firefox/144.0",
              "object_type": "publication",
              "object_id": "$publicationUuid",
              "target_url": "https://parti-renaissance.fr"
            }
            JSON
        );

        $this->assertResponseStatusCode(Response::HTTP_ACCEPTED, $this->client->getResponse());

        $stats = $this->aggregator->getStats(TargetTypeEnum::Publication, $publicationUuid);

        PHPUnitHelper::assertArraySubset([
            'contacts' => 0,
            'notifications' => [
                'android' => 0,
                'ios' => 0,
                'web' => 0,
            ],
            'unique_clicks' => [
                'app' => 1,
                'app_rate' => 100.0,
                'email' => 0,
                'email_rate' => 0.0,
                'total' => 1,
                'total_rate' => 100.0,
            ],
            'unique_emails' => null,
            'unique_impressions' => [
                'list' => 0,
                'timeline' => 1,
                'total' => 1,
            ],
            'unique_notifications' => 0,
            'unique_opens' => [
                'app' => 1,
                'app_rate' => 100.0,
                'direct_link' => 0,
                'email' => 0,
                'email_rate' => 0.0,
                'list' => 0,
                'notification' => 0,
                'notification_rate' => 0.0,
                'timeline' => 1,
                'total' => 1,
                'total_rate' => 100.0,
            ],
            'unsubscribed' => [
                'total' => 0,
                'total_rate' => 0.0,
            ],
            'visible_count' => 4,
        ], $stats->jsonSerialize());
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->aggregator = self::getContainer()->get(AggregatorInterface::class);

        $this->accessToken = $this->getAccessToken(
            LoadClientData::CLIENT_13_UUID,
            'BHLfR-MWLVBF@Z.ZBh4EdTFJ15',
            GrantTypeEnum::PASSWORD,
            Scope::JEMARCHE_APP,
            'president-ad@renaissance-dev.fr',
            LoadAdherentData::DEFAULT_PASSWORD
        );
    }
}
