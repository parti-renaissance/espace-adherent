<?php

namespace Tests\App\Controller\Api\Event;

use App\AppCodeEnum;
use App\DataFixtures\ORM\LoadAdherentData;
use App\DataFixtures\ORM\LoadClientData;
use App\Entity\Event\BaseEvent;
use App\Event\EventTypeEnum;
use App\Mailer\Message\JeMengage\JeMengageEventUpdateMessage;
use App\OAuth\Model\GrantTypeEnum;
use App\OAuth\Model\Scope;
use Symfony\Component\HttpFoundation\Request;
use Tests\App\AbstractWebCaseTest as WebTestCase;
use Tests\App\Controller\ApiControllerTestTrait;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group functional
 * @group api
 */
class EventControllerTest extends WebTestCase
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
            'referent@en-marche-dev.fr',
            LoadAdherentData::DEFAULT_PASSWORD
        );

        $this->client->request(Request::METHOD_POST, '/api/v3/events', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => "Bearer $accessToken",
        ], json_encode([
            'type' => EventTypeEnum::TYPE_DEFAULT,
            'category' => 'kiosque',
            'name' => 'My best event !',
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.',
            'time_zone' => 'Europe/Paris',
            'begin_at' => (new \DateTime('+5 days'))->format('Y-m-d').' 10:30:00',
            'finish_at' => (new \DateTime('+5 days'))->format('Y-m-d').' 16:30:00',
            'capacity' => 10,
            'visio_url' => 'https://en-marche.fr/123',
            'mode' => BaseEvent::MODE_ONLINE,
       ]));

        $this->assertResponseStatusCodeSame(201);

        $response = json_decode($this->client->getResponse()->getContent(), true);

        self::assertSame('My best event !', $response['name']);
        self::assertSame('online', $response['mode']);

        $registration = $this->getEventRegistrationRepository()->findAdherentRegistration($response['uuid'], $response['organizer']['uuid']);

        self::assertSame(AppCodeEnum::JEMENGAGE_WEB, $registration->getSource());

        $this->client->request(Request::METHOD_PUT, sprintf('/api/v3/events/%s', $response['uuid']), [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => "Bearer $accessToken",
        ], json_encode([
            'name' => 'My best edited event !',
            'begin_at' => (new \DateTime('+5 days'))->format('Y-m-d').' 11:30:00',
        ]));

        $this->assertResponseStatusCodeSame(200);

        $response = json_decode($this->client->getResponse()->getContent(), true);

        self::assertSame('My best edited event !', $response['name']);
        self::assertSame('online', $response['mode']);

        $this->assertCountMails(1, JeMengageEventUpdateMessage::class);
        $this->assertMail(JeMengageEventUpdateMessage::class, 'referent@en-marche-dev.fr', ['template_name' => 'je-mengage-event-update']);
    }
}
