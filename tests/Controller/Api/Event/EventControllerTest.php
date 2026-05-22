<?php

declare(strict_types=1);

namespace Tests\App\Controller\Api\Event;

use App\AppCodeEnum;
use App\DataFixtures\ORM\LoadAdherentData;
use App\DataFixtures\ORM\LoadClientData;
use App\Entity\Event\Event;
use App\Entity\Geo\Zone;
use App\Mailer\Message\Renaissance\EventUpdateMessage;
use App\Mailer\Message\Renaissance\RenaissanceEventNotificationMessage;
use App\OAuth\Model\GrantTypeEnum;
use App\OAuth\Model\Scope;
use App\Scope\ScopeEnum;
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

    /**
     * Regression: the jemarche militant app POSTs events without ?scope=. The author scope was then
     * left null, which broke the author's own management (editable:false under ?scope=militant) and
     * crashed the creation push notification ("no assembly zone"). The militant scope must be applied
     * by default: author_* set to militant + the event attached to the militant's commune.
     */
    public function testAPureMilitantCanCreateAndManageAnEventWithoutScopeParam(): void
    {
        $accessToken = $this->getAccessToken(
            LoadClientData::CLIENT_13_UUID,
            'BHLfR-MWLVBF@Z.ZBh4EdTFJ15',
            GrantTypeEnum::PASSWORD,
            Scope::JEMARCHE_APP,
            'carl999@example.fr', // pure militant
            LoadAdherentData::DEFAULT_PASSWORD
        );

        // No ?scope= on purpose: this is exactly what the militant app sends.
        $this->client->request(Request::METHOD_POST, '/api/v3/events', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => "Bearer $accessToken",
        ], json_encode([
            'category' => 'kiosque',
            'name' => 'Militant event without scope',
            'description' => str_repeat('Lorem ipsum dolor sit amet. ', 20),
            'time_zone' => 'Europe/Paris',
            'begin_at' => new \DateTime('+5 days')->format('Y-m-d').' 10:30:00',
            'finish_at' => new \DateTime('+5 days')->format('Y-m-d').' 16:30:00',
            'mode' => Event::MODE_MEETING,
            'visibility' => 'public',
            'post_address' => [
                'address' => '47 rue Martre',
                'postal_code' => '92110',
                'city_name' => 'Clichy',
                'country' => 'FR',
            ],
        ]));

        // No more 500 from EventCreatedNotification ("no assembly zone").
        self::assertResponseStatusCodeSame(201);
        $uuid = json_decode($this->client->getResponse()->getContent(), true)['uuid'];

        // The militant scope was applied by default: author_* set + commune zone attached.
        $manager = static::getContainer()->get('doctrine')->getManager();
        $manager->clear();
        $event = $manager->getRepository(Event::class)->findOneBy(['name' => 'Militant event without scope']);

        self::assertSame(ScopeEnum::MILITANT, $event->getAuthorScope());
        self::assertNotEmpty($event->getZones()->toArray(), 'The militant event must be attached to its commune');

        // The author can manage their own event in the militant scope (the reported bug).
        $this->client->request(Request::METHOD_GET, \sprintf('/api/v3/events/%s?scope=militant', $uuid), [], [], [
            'HTTP_AUTHORIZATION' => "Bearer $accessToken",
        ]);

        self::assertResponseIsSuccessful();
        self::assertTrue(json_decode($this->client->getResponse()->getContent(), true)['editable']);
    }

    /**
     * The militant default still enforces MilitantEventCreation: a non-pure militant (cadre) cannot
     * create a militant event by simply omitting ?scope=.
     */
    public function testCreatingAMilitantEventWithoutScopeIsRejectedForNonPureMilitant(): void
    {
        $accessToken = $this->getAccessToken(
            LoadClientData::CLIENT_13_UUID,
            'BHLfR-MWLVBF@Z.ZBh4EdTFJ15',
            GrantTypeEnum::PASSWORD,
            Scope::JEMARCHE_APP,
            'president-ad@renaissance-dev.fr', // not a pure militant
            LoadAdherentData::DEFAULT_PASSWORD
        );

        $this->client->request(Request::METHOD_POST, '/api/v3/events', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => "Bearer $accessToken",
        ], json_encode([
            'category' => 'kiosque',
            'name' => 'Cadre militant event without scope',
            'description' => str_repeat('Lorem ipsum dolor sit amet. ', 20),
            'time_zone' => 'Europe/Paris',
            'begin_at' => new \DateTime('+5 days')->format('Y-m-d').' 10:30:00',
            'finish_at' => new \DateTime('+5 days')->format('Y-m-d').' 16:30:00',
            'mode' => Event::MODE_MEETING,
            'visibility' => 'public',
            'post_address' => [
                'address' => '47 rue Martre',
                'postal_code' => '92110',
                'city_name' => 'Clichy',
                'country' => 'FR',
            ],
        ]));

        self::assertResponseStatusCodeSame(400);
        $violations = json_decode($this->client->getResponse()->getContent(), true)['violations'];
        self::assertSame(
            'Seul un adhérent sans responsabilité cadre peut créer un événement militant.',
            $violations[0]['message']
        );
    }

    /**
     * A militant online event whose author has no city zone (here a foreign-resident militant, who
     * never has a French commune) has no geographic audience: the creation push notification is
     * skipped (SendEventPushNotificationListener) instead of crashing on the missing assembly zone.
     * The event is still created with the militant scope.
     */
    public function testAMilitantOnlineEventWithoutCommuneHasNoAudienceAndIsCreated(): void
    {
        $accessToken = $this->getAccessToken(
            LoadClientData::CLIENT_13_UUID,
            'BHLfR-MWLVBF@Z.ZBh4EdTFJ15',
            GrantTypeEnum::PASSWORD,
            Scope::JEMARCHE_APP,
            'damien.schmidt@example.ch', // foreign-resident pure militant: no French commune
            LoadAdherentData::DEFAULT_PASSWORD
        );

        // Online event, no post_address and no ?scope=.
        $this->client->request(Request::METHOD_POST, '/api/v3/events', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => "Bearer $accessToken",
        ], json_encode([
            'category' => 'kiosque',
            'name' => 'Militant online event without commune',
            'description' => str_repeat('Lorem ipsum dolor sit amet. ', 20),
            'time_zone' => 'Europe/Paris',
            'begin_at' => new \DateTime('+5 days')->format('Y-m-d').' 10:30:00',
            'finish_at' => new \DateTime('+5 days')->format('Y-m-d').' 16:30:00',
            'mode' => Event::MODE_ONLINE,
            'visio_url' => 'https://example.test/visio',
            'visibility' => 'public',
        ]));

        // No crash from the notification despite having no zone.
        self::assertResponseStatusCodeSame(201);

        $manager = static::getContainer()->get('doctrine')->getManager();
        $manager->clear();
        $event = $manager->getRepository(Event::class)->findOneBy(['name' => 'Militant online event without commune']);

        self::assertSame(ScopeEnum::MILITANT, $event->getAuthorScope());
        self::assertEmpty($event->getZones()->toArray(), 'An online militant event without a commune has no zone');
    }

    /**
     * An online militant event (no address) is attached to the author's own commune — the militant
     * scope is zone-less, so the city comes from the adherent. Uses a geocoded pure militant.
     */
    public function testAMilitantOnlineEventIsAttachedToTheAuthorCommune(): void
    {
        $accessToken = $this->getAccessToken(
            LoadClientData::CLIENT_13_UUID,
            'BHLfR-MWLVBF@Z.ZBh4EdTFJ15',
            GrantTypeEnum::PASSWORD,
            Scope::JEMARCHE_APP,
            'adherent-male-a@en-marche-dev.fr', // pure militant geocoded in Melun
            LoadAdherentData::DEFAULT_PASSWORD
        );

        // Online event (no post_address), no ?scope=.
        $this->client->request(Request::METHOD_POST, '/api/v3/events', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => "Bearer $accessToken",
        ], json_encode([
            'category' => 'kiosque',
            'name' => 'Militant online event with commune',
            'description' => str_repeat('Lorem ipsum dolor sit amet. ', 20),
            'time_zone' => 'Europe/Paris',
            'begin_at' => new \DateTime('+5 days')->format('Y-m-d').' 10:30:00',
            'finish_at' => new \DateTime('+5 days')->format('Y-m-d').' 16:30:00',
            'mode' => Event::MODE_ONLINE,
            'visio_url' => 'https://example.test/visio',
            'visibility' => 'public',
        ]));

        self::assertResponseStatusCodeSame(201);

        $manager = static::getContainer()->get('doctrine')->getManager();
        $manager->clear();
        $event = $manager->getRepository(Event::class)->findOneBy(['name' => 'Militant online event with commune']);

        self::assertSame(ScopeEnum::MILITANT, $event->getAuthorScope());

        $cityZones = array_values(array_filter(
            $event->getZones()->toArray(),
            static fn (Zone $zone) => Zone::CITY === $zone->getType(),
        ));
        self::assertCount(1, $cityZones, 'The online militant event must be attached to the author commune');
        self::assertSame('Melun', $cityZones[0]->getName());
    }

    public function testAMilitantCannotCreateANonPublicEventWithoutScope(): void
    {
        $accessToken = $this->getAccessToken(
            LoadClientData::CLIENT_13_UUID,
            'BHLfR-MWLVBF@Z.ZBh4EdTFJ15',
            GrantTypeEnum::PASSWORD,
            Scope::JEMARCHE_APP,
            'carl999@example.fr', // pure militant: only the visibility rule should fail
            LoadAdherentData::DEFAULT_PASSWORD
        );

        $this->client->request(Request::METHOD_POST, '/api/v3/events', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => "Bearer $accessToken",
        ], json_encode([
            'category' => 'kiosque',
            'name' => 'Private militant event',
            'description' => str_repeat('Lorem ipsum dolor sit amet. ', 20),
            'time_zone' => 'Europe/Paris',
            'begin_at' => new \DateTime('+5 days')->format('Y-m-d').' 10:30:00',
            'finish_at' => new \DateTime('+5 days')->format('Y-m-d').' 16:30:00',
            'mode' => Event::MODE_MEETING,
            'visibility' => 'private',
            'post_address' => [
                'address' => '47 rue Martre',
                'postal_code' => '92110',
                'city_name' => 'Clichy',
                'country' => 'FR',
            ],
        ]));

        self::assertResponseStatusCodeSame(400);
        $violations = json_decode($this->client->getResponse()->getContent(), true)['violations'];
        self::assertSame('Un événement militant doit être public et répertorié.', $violations[0]['message']);
    }

    public function testAPureMilitantCanCreateAndManageAnEventWithExplicitMilitantScope(): void
    {
        $accessToken = $this->getAccessToken(
            LoadClientData::CLIENT_13_UUID,
            'BHLfR-MWLVBF@Z.ZBh4EdTFJ15',
            GrantTypeEnum::PASSWORD,
            Scope::JEMARCHE_APP,
            'carl999@example.fr',
            LoadAdherentData::DEFAULT_PASSWORD
        );

        $this->client->request(Request::METHOD_POST, '/api/v3/events?scope=militant', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => "Bearer $accessToken",
        ], json_encode([
            'category' => 'kiosque',
            'name' => 'Explicit militant scope event',
            'description' => str_repeat('Lorem ipsum dolor sit amet. ', 20),
            'time_zone' => 'Europe/Paris',
            'begin_at' => new \DateTime('+5 days')->format('Y-m-d').' 10:30:00',
            'finish_at' => new \DateTime('+5 days')->format('Y-m-d').' 16:30:00',
            'mode' => Event::MODE_MEETING,
            'visibility' => 'public',
            'post_address' => [
                'address' => '47 rue Martre',
                'postal_code' => '92110',
                'city_name' => 'Clichy',
                'country' => 'FR',
            ],
        ]));

        self::assertResponseStatusCodeSame(201);
        $uuid = json_decode($this->client->getResponse()->getContent(), true)['uuid'];

        $this->client->request(Request::METHOD_GET, \sprintf('/api/v3/events/%s?scope=militant', $uuid), [], [], [
            'HTTP_AUTHORIZATION' => "Bearer $accessToken",
        ]);

        self::assertResponseIsSuccessful();
        self::assertTrue(json_decode($this->client->getResponse()->getContent(), true)['editable']);
    }

    /**
     * Only the author manages their own militant event: any other militant gets editable:false and a
     * forbidden PUT, while the author can edit it as long as the militant scope is activated.
     */
    public function testOnlyTheAuthorCanManageTheirMilitantEvent(): void
    {
        $authorToken = $this->getAccessToken(
            LoadClientData::CLIENT_13_UUID,
            'BHLfR-MWLVBF@Z.ZBh4EdTFJ15',
            GrantTypeEnum::PASSWORD,
            Scope::JEMARCHE_APP,
            'carl999@example.fr',
            LoadAdherentData::DEFAULT_PASSWORD
        );

        $this->client->request(Request::METHOD_POST, '/api/v3/events', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => "Bearer $authorToken",
        ], json_encode([
            'category' => 'kiosque',
            'name' => 'Authored militant event',
            'description' => str_repeat('Lorem ipsum dolor sit amet. ', 20),
            'time_zone' => 'Europe/Paris',
            'begin_at' => new \DateTime('+5 days')->format('Y-m-d').' 10:30:00',
            'finish_at' => new \DateTime('+5 days')->format('Y-m-d').' 16:30:00',
            'mode' => Event::MODE_MEETING,
            'visibility' => 'public',
            'post_address' => [
                'address' => '47 rue Martre',
                'postal_code' => '92110',
                'city_name' => 'Clichy',
                'country' => 'FR',
            ],
        ]));

        self::assertResponseStatusCodeSame(201);
        $uuid = json_decode($this->client->getResponse()->getContent(), true)['uuid'];

        // The author can manage it under the militant scope.
        $this->client->request(Request::METHOD_GET, \sprintf('/api/v3/events/%s?scope=militant', $uuid), [], [], [
            'HTTP_AUTHORIZATION' => "Bearer $authorToken",
        ]);
        self::assertResponseIsSuccessful();
        self::assertTrue(json_decode($this->client->getResponse()->getContent(), true)['editable']);

        // Another militant can read it but cannot manage it.
        $otherToken = $this->getAccessToken(
            LoadClientData::CLIENT_13_UUID,
            'BHLfR-MWLVBF@Z.ZBh4EdTFJ15',
            GrantTypeEnum::PASSWORD,
            Scope::JEMARCHE_APP,
            'simple-user@example.ch',
            LoadAdherentData::DEFAULT_PASSWORD
        );

        $this->client->request(Request::METHOD_GET, \sprintf('/api/v3/events/%s?scope=militant', $uuid), [], [], [
            'HTTP_AUTHORIZATION' => "Bearer $otherToken",
        ]);
        self::assertResponseIsSuccessful();
        self::assertFalse(json_decode($this->client->getResponse()->getContent(), true)['editable']);

        // A non-author cannot edit the event.
        $this->client->request(Request::METHOD_PUT, \sprintf('/api/v3/events/%s?scope=militant', $uuid), [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => "Bearer $otherToken",
        ], json_encode(['name' => 'Hijacked event']));
        self::assertResponseStatusCodeSame(403);

        // The author can edit it.
        $this->client->request(Request::METHOD_PUT, \sprintf('/api/v3/events/%s?scope=militant', $uuid), [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => "Bearer $authorToken",
        ], json_encode([
            'name' => 'Authored militant event (edited)',
        ]));
        self::assertResponseIsSuccessful();
        self::assertSame('Authored militant event (edited)', json_decode($this->client->getResponse()->getContent(), true)['name']);
    }
}
