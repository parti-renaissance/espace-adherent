<?php

declare(strict_types=1);

namespace Tests\App\Controller\Api\Event;

use App\AppCodeEnum;
use App\DataFixtures\ORM\LoadAdherentData;
use App\DataFixtures\ORM\LoadClientData;
use App\Entity\Event\Event;
use App\Mailer\Message\Renaissance\EventUpdateMessage;
use App\Mailer\Message\Renaissance\RenaissanceEventNotificationMessage;
use App\OAuth\Model\GrantTypeEnum;
use App\OAuth\Model\Scope;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;
use Tests\App\AbstractApiTestCase;
use Tests\App\Controller\ApiControllerTestTrait;
use Tests\App\Controller\ControllerTestTrait;

#[Group('functional')]
#[Group('api')]
class EventControllerTest extends AbstractApiTestCase
{
    use ControllerTestTrait;
    use ApiControllerTestTrait;

    public function testAReferentCanCreateAnEventInJme(): void
    {
        $accessToken = $this->getAccessToken(
            LoadClientData::CLIENT_12_UUID,
            'BHLfR-MWLVBF@Z.ZBh4EdTFJ',
            GrantTypeEnum::PASSWORD,
            Scope::JEMENGAGE_ADMIN,
            'president-ad@renaissance-dev.fr',
            LoadAdherentData::DEFAULT_PASSWORD
        );

        $this->client->request(Request::METHOD_POST, '/api/v3/events?scope=president_departmental_assembly', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => "Bearer $accessToken",
        ], json_encode([
            'category' => 'kiosque',
            'name' => 'My best event !',
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.',
            'time_zone' => 'Europe/Paris',
            'begin_at' => new \DateTime('+5 days')->format('Y-m-d').' 10:30:00',
            'finish_at' => new \DateTime('+5 days')->format('Y-m-d').' 16:30:00',
            'capacity' => 10,
            'visio_url' => 'https://en-marche.fr/123',
            'mode' => Event::MODE_ONLINE,
        ]));

        self::assertResponseStatusCodeSame(201);

        $response = json_decode($this->client->getResponse()->getContent(), true);

        self::assertSame('My best event !', $response['name']);
        self::assertSame('online', $response['mode']);

        $registration = $this->getEventRegistrationRepository()->findAdherentRegistration($response['uuid'], $response['organizer']['uuid']);

        $this->assertCountMails(1, RenaissanceEventNotificationMessage::class);
        $this->assertCount(3, $this->getMailMessages(RenaissanceEventNotificationMessage::class)[0]->getRecipients());

        self::assertSame(AppCodeEnum::JEMENGAGE_WEB, $registration->getSource());

        $this->client->request(Request::METHOD_PUT, \sprintf('/api/v3/events/%s?scope=president_departmental_assembly', $response['uuid']), [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => "Bearer $accessToken",
        ], json_encode([
            'name' => 'My best edited event !',
            'begin_at' => new \DateTime('+5 days')->format('Y-m-d').' 11:30:00',
        ]));

        self::assertResponseStatusCodeSame(200);

        $response = json_decode($this->client->getResponse()->getContent(), true);

        self::assertSame('My best edited event !', $response['name']);
        self::assertSame('online', $response['mode']);

        $this->assertCountMails(1, EventUpdateMessage::class);
        $this->assertMail(EventUpdateMessage::class, 'president-ad@renaissance-dev.fr', ['template_name' => 'event-update']);
    }
}
