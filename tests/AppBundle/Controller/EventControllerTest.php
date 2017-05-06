<?php

namespace Tests\AppBundle\Controller;

use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\DataFixtures\ORM\LoadEventData;
use AppBundle\DataFixtures\ORM\LoadHomeBlockData;
use AppBundle\Entity\EventInvite;
use AppBundle\Entity\EventRegistration;
use AppBundle\Mailjet\Message\EventCancellationMessage;
use AppBundle\Mailjet\Message\EventContactMembersMessage;
use AppBundle\Mailjet\Message\EventInvitationMessage;
use AppBundle\Mailjet\Message\EventRegistrationConfirmationMessage;
use AppBundle\Repository\EventRegistrationRepository;
use Ramsey\Uuid\Uuid;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\MysqlWebTestCase;

/**
 * @group functionnal
 */
class EventControllerTest extends MysqlWebTestCase
{
    use ControllerTestTrait;

    /** @var EventRegistrationRepository */
    private $repository;

    public function testAnonymousUserCanRegisterToEvent()
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $crawler = $this->client->click($crawler->selectLink('Rejoindre un comité')->link());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame('En Marche Paris 8', trim($crawler->filter('.search__results__meta > h2')->text()));

        $crawler = $this->client->click($crawler->filter('.search__committee__box')->link());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame('1 / 50 inscrits', trim($crawler->filter('.committee-event-attendees')->text()));

        $crawler = $this->client->click($crawler->filter('.committee-event-more')->link());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $crawler = $this->client->click($crawler->selectLink('Je veux participer')->link());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertEmpty($crawler->filter('#field-first-name > input[type="text"]')->attr('value'));
        $this->assertEmpty($crawler->filter('#field-postal-code > input[type="text"]')->attr('value'));
        $this->assertEmpty($crawler->filter('#field-email-address > input[type="email"]')->attr('value'));

        $crawler = $this->client->submit($crawler->selectButton("Je m'inscris")->form());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame(3, $crawler->filter('.form__errors')->count());
        $this->assertSame('Cette valeur ne doit pas être vide.', $crawler->filter('#field-first-name .form__errors > li')->text());
        $this->assertSame('Cette valeur ne doit pas être vide.', $crawler->filter('#field-postal-code .form__errors > li')->text());
        $this->assertSame('Cette valeur ne doit pas être vide.', $crawler->filter('#field-email-address .form__errors > li')->text());

        $this->client->submit($crawler->selectButton("Je m'inscris")->form([
            'event_registration' => [
                'firstName' => 'Pauline',
                'emailAddress' => 'paupau75@example.org',
                'postalCode' => '75001',
                'newsletterSubscriber' => true,
            ],
        ]));

        $this->assertInstanceOf(EventRegistration::class, $this->repository->findGuestRegistration(LoadEventData::EVENT_1_UUID, 'paupau75@example.org'));
        $this->assertCount(1, $this->getMailjetEmailRepository()->findRecipientMessages(EventRegistrationConfirmationMessage::class, 'paupau75@example.org'));

        $crawler = $this->client->followRedirect();

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertTrue($this->seeMessageSuccesfullyCreatedFlash($crawler, "Votre inscription à l'événement est confirmée."));
        $this->assertContains('Votre participation est bien enregistrée !', $crawler->filter('.committee-event-registration-confirmation p')->text());

        $crawler = $this->client->click($crawler->selectLink("Retour à l'événement")->link());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame('2 / 50 inscrits', trim($crawler->filter('.committee-event-attendees')->text()));
    }

    public function testRegisteredAdherentUserCanRegisterToEvent()
    {
        $crawler = $this->authenticateAsAdherent($this->client, 'benjyd@aol.com', 'HipHipHip');

        $crawler = $this->client->click($crawler->selectLink('Rejoindre un comité')->link());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame('En Marche Paris 8', trim($crawler->filter('.search__results__meta > h2')->text()));

        $crawler = $this->client->click($crawler->filter('.search__committee__box')->link());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame('1 / 50 inscrits', trim($crawler->filter('.committee-event-attendees')->text()));

        $crawler = $this->client->click($crawler->filter('.committee-event-more')->link());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $crawler = $this->client->click($crawler->selectLink('Je veux participer')->link());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame('Benjamin', $crawler->filter('#field-first-name > input[type="text"]')->attr('value'));
        $this->assertSame('13003', $crawler->filter('#field-postal-code > input[type="text"]')->attr('value'));
        $this->assertSame('benjyd@aol.com', $crawler->filter('#field-email-address > input[type="email"]')->attr('value'));

        $this->client->submit($crawler->selectButton("Je m'inscris")->form());

        $this->assertInstanceOf(EventRegistration::class, $this->repository->findGuestRegistration(LoadEventData::EVENT_1_UUID, 'benjyd@aol.com'));
        $this->assertCount(1, $this->getMailjetEmailRepository()->findRecipientMessages(EventRegistrationConfirmationMessage::class, 'benjyd@aol.com'));

        $crawler = $this->client->followRedirect();

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertTrue($this->seeMessageSuccesfullyCreatedFlash($crawler, "Votre inscription à l'événement est confirmée."));
        $this->assertContains('Votre participation est bien enregistrée !', $crawler->filter('.committee-event-registration-confirmation p')->text());

        $crawler = $this->client->click($crawler->selectLink("Retour à l'événement")->link());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame('2 / 50 inscrits', trim($crawler->filter('.committee-event-attendees')->text()));

        $this->client->click($crawler->selectLink('Mes événements')->link());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertContains('Réunion de réflexion parisienne', $this->client->getResponse()->getContent());
    }

    public function testCantRegisterToAFullEvent()
    {
        $this->authenticateAsAdherent($this->client, 'benjyd@aol.com', 'HipHipHip');

        $eventUrl = '/evenements/'.LoadEventData::EVENT_5_UUID.'/'.date('Y-m-d', strtotime('+17 days')).'-reunion-de-reflexion-marseillaise';
        $crawler = $this->client->request('GET', $eventUrl);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $headerText = $crawler->filter('.committee__event__header__cta')->text();
        $this->assertContains('1 / 1 inscrit', $headerText);
        $this->assertNotContains('JE VEUX PARTICIPER', $headerText);

        $crawler = $this->client->request('GET', $eventUrl.'/inscription');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->assertSame('Benjamin', $crawler->filter('#field-first-name > input[type="text"]')->attr('value'));
        $this->assertSame('13003', $crawler->filter('#field-postal-code > input[type="text"]')->attr('value'));
        $this->assertSame('benjyd@aol.com', $crawler->filter('#field-email-address > input[type="email"]')->attr('value'));

        $crawler = $this->client->submit($crawler->selectButton("Je m'inscris")->form());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertContains("L'événement est complet.", $crawler->filter('.form__errors')->text());
    }

    /**
     * @dataProvider provideHostProtectedPages
     */
    public function testAnonymousUserCannotEditEvent($path)
    {
        $this->client->request('GET', $path);
        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
    }

    /**
     * @dataProvider provideCancelledInaccessiblePages
     */
    public function testRegisteredAdherentUserCannotFoundPagesOfCancelledEvent($path)
    {
        $this->authenticateAsAdherent($this->client, 'benjyd@aol.com', 'HipHipHip');
        $this->client->request(Request::METHOD_GET, $path);
        $this->assertResponseStatusCode(Response::HTTP_NOT_FOUND, $this->client->getResponse());
    }

    /**
     * @dataProvider provideHostProtectedPages
     */
    public function testRegisteredAdherentUserCannotEditEvent($path)
    {
        $this->authenticateAsAdherent($this->client, 'benjyd@aol.com', 'HipHipHip');
        $this->client->request('GET', $path);
        $this->assertResponseStatusCode(Response::HTTP_FORBIDDEN, $this->client->getResponse());
    }

    public function provideHostProtectedPages()
    {
        $uuid = LoadEventData::EVENT_1_UUID;
        $slug = date('Y-m-d', strtotime('+3 days')).'-reunion-de-reflexion-parisienne';

        return [
            ['/evenements/'.$uuid.'/'.$slug.'/modifier'],
            ['/evenements/'.$uuid.'/'.$slug.'/inscrits'],
        ];
    }

    public function provideCancelledInaccessiblePages()
    {
        $uuid = LoadEventData::EVENT_6_UUID;
        $slug = date('Y-m-d', strtotime('+60 days')).'-reunion-de-reflexion-parisienne-annule';

        return [
            ['/evenements/'.$uuid.'/'.$slug.'/modifier'],
            ['/evenements/'.$uuid.'/'.$slug.'/inscription'],
            ['/evenements/'.$uuid.'/'.$slug.'/annuler'],
        ];
    }

    public function testOrganizerCanEditEvent()
    {
        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr', 'changeme1337');

        $crawler = $this->client->request('GET', '/evenements/'.LoadEventData::EVENT_1_UUID.'/'.date('Y-m-d', strtotime('+3 days')).'-reunion-de-reflexion-parisienne/modifier');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->submit($crawler->selectButton('Enregistrer')->form([
            'committee_event' => [
                'name' => "Débat sur l'écologie",
                'description' => 'Cette journée sera consacrée à un grand débat sur la question écologique.',
                'category' => 'CE003',
                'address' => [
                    'address' => '6 rue Neyret',
                    'country' => 'FR',
                    'postalCode' => '69001',
                    'city' => '69001-69381',
                    'cityName' => '',
                ],
                'beginAt' => [
                    'date' => [
                        'year' => '2022',
                        'month' => '3',
                        'day' => '2',
                    ],
                    'time' => [
                        'hour' => '9',
                        'minute' => '30',
                    ],
                ],
                'finishAt' => [
                    'date' => [
                        'year' => '2022',
                        'month' => '3',
                        'day' => '2',
                    ],
                    'time' => [
                        'hour' => '19',
                        'minute' => '0',
                    ],
                ],
                'capacity' => '1500',
            ],
        ]));

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);

        // Follow the redirect and check the adherent can see the committee page
        $crawler = $this->client->followRedirect();
        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertContains('L\'événement a bien été modifié.', $crawler->filter('#notice-flashes')->text());
        $this->assertSame('Débat sur l\'écologie | En Marche !', $crawler->filter('title')->text());
        $this->assertSame('Débat sur l\'écologie', $crawler->filter('.committee-event-name')->text());
        $this->assertSame('Organisé par Jacques Picard du comité En Marche Paris 8', trim(preg_replace('/\s+/', ' ', $crawler->filter('.committee-event-organizer')->text())));
        $this->assertSame('Mercredi 2 mars 2022, 9h30', $crawler->filter('.committee-event-date')->text());
        $this->assertSame('6 rue Neyret, 69001 Lyon 1er', $crawler->filter('.committee-event-address')->text());
        $this->assertSame('Cette journée sera consacrée à un grand débat sur la question écologique.', $crawler->filter('.committee-event-description')->text());
    }

    public function testOrganizerCanCancelEvent()
    {
        $this->authenticateAsAdherent($this->client, 'francis.brioul@yahoo.com', 'Champion20');

        $crawler = $this->client->request(Request::METHOD_GET, '/evenements/'.LoadEventData::EVENT_2_UUID.'/'.date('Y-m-d', strtotime('+10 days')).'-reunion-de-reflexion-dammarienne/annuler');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->submit($crawler->selectButton('Oui, annuler l\'événement')->form());

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);

        // Follow the redirect and check the adherent can see the committee page
        $crawler = $this->client->followRedirect();
        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        static::assertContains('L\'événement a bien été annulé.', $crawler->filter('#notice-flashes')->text());

        $messages = $this->getMailjetEmailRepository()->findMessages(EventCancellationMessage::class);
        /** @var EventCancellationMessage $message */
        $message = array_shift($messages);

        // Two mails have been sent
        static::assertCount(2, $message->getRecipients());
    }

    public function testCommitteeHostCanEditEvent()
    {
        $this->authenticateAsAdherent($this->client, 'gisele-berthoux@caramail.com', 'ILoveYouManu');

        $this->client->request('GET', '/evenements/'.LoadEventData::EVENT_1_UUID.'/'.date('Y-m-d', strtotime('+3 days')).'-reunion-de-reflexion-parisienne/modifier');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
    }

    public function testOrganizerCanSeeRegistrations()
    {
        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr', 'changeme1337');
        $crawler = $this->client->request('GET', '/evenements/'.LoadEventData::EVENT_1_UUID.'/'.date('Y-m-d', strtotime('+3 days')).'-reunion-de-reflexion-parisienne');
        $crawler = $this->client->click($crawler->selectLink('Gérer les participants')->link());

        $this->assertTrue($this->seeMembersList($crawler, 2));
    }

    public function testOrganizerCanExportRegistrations()
    {
        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr', 'changeme1337');
        $crawler = $this->client->request('GET', '/evenements/'.LoadEventData::EVENT_1_UUID.'/'.date('Y-m-d', strtotime('+3 days')).'-reunion-de-reflexion-parisienne');
        $crawler = $this->client->click($crawler->selectLink('Gérer les participants')->link());

        $token = $crawler->filter('#members-export-token')->attr('value');
        $uuids = (array) $crawler->filter('input[name="members[]"]')->attr('value');

        $exportUrl = $this->client->getRequest()->getPathInfo().'/exporter';

        $this->client->request(Request::METHOD_POST, $exportUrl, [
            'token' => $token,
            'exports' => json_encode($uuids),
        ]);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertCount(3, explode("\n", $this->client->getResponse()->getContent()));

        // Try to illegally export an adherent data
        $uuids[] = Uuid::uuid4();

        $this->client->request(Request::METHOD_POST, $exportUrl, [
            'token' => $token,
            'exports' => json_encode($uuids),
        ]);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertCount(3, explode("\n", $this->client->getResponse()->getContent()));

        $this->client->request(Request::METHOD_POST, $exportUrl, [
            'token' => $token,
            'exports' => json_encode([]),
        ]);

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
    }

    public function testOrganizerCanContactRegistrations()
    {
        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr', 'changeme1337');
        $crawler = $this->client->request('GET', '/evenements/'.LoadEventData::EVENT_1_UUID.'/'.date('Y-m-d', strtotime('+3 days')).'-reunion-de-reflexion-parisienne');
        $crawler = $this->client->click($crawler->selectLink('Gérer les participants')->link());

        $token = $crawler->filter('#members-contact-token')->attr('value');
        $uuids = (array) $crawler->filter('input[name="members[]"]')->attr('value');

        $membersUrl = $this->client->getRequest()->getPathInfo();
        $contactUrl = $membersUrl.'/contacter';

        $crawler = $this->client->request(Request::METHOD_POST, $contactUrl, [
            'token' => $token,
            'contacts' => json_encode($uuids),
        ]);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        // Try to post with an empty message
        $crawler = $this->client->request(Request::METHOD_POST, $contactUrl, [
            'token' => $crawler->filter('input[name="token"]')->attr('value'),
            'contacts' => $crawler->filter('input[name="contacts"]')->attr('value'),
            'message' => ' ',
        ]);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame('Cette valeur ne doit pas être vide.', $crawler->filter('.form__errors > .form__error')->text());

        $this->client->request(Request::METHOD_POST, $contactUrl, [
            'token' => $crawler->filter('input[name="token"]')->attr('value'),
            'contacts' => $crawler->filter('input[name="contacts"]')->attr('value'),
            'message' => 'Hello!',
        ]);

        $this->assertClientIsRedirectedTo($membersUrl, $this->client);

        $crawler = $this->client->followRedirect();

        $this->seeMessageSuccesfullyCreatedFlash($crawler, 'Félicitations, votre message a bien été envoyé aux inscrits sélectionnés.');

        // Email should have been sent
        $this->assertCount(1, $this->getMailjetEmailRepository()->findMessages(EventContactMembersMessage::class));

        // Try to illegally contact an adherent
        $uuids[] = Uuid::uuid4();

        $crawler = $this->client->request(Request::METHOD_POST, $contactUrl, [
            'token' => $token,
            'contacts' => json_encode($uuids),
        ]);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertCount(1, json_decode($crawler->filter('input[name="contacts"]')->attr('value'), true));

        // Force the contact form with foreign uuid
        $this->client->request(Request::METHOD_POST, $contactUrl, [
            'token' => $crawler->filter('input[name="token"]')->attr('value'),
            'contacts' => json_encode($uuids),
            'message' => 'Hello!',
        ]);

        $this->assertClientIsRedirectedTo($membersUrl, $this->client);
    }

    public function testExportIcalEvent()
    {
        $this->client->request('GET', '/evenements/'.LoadEventData::EVENT_1_UUID.'/'.date('Y-m-d', strtotime('+3 days')).'-reunion-de-reflexion-parisienne/ical');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
    }

    public function testAdherentCanInviteToEvent()
    {
        $this->authenticateAsAdherent($this->client, 'carl999@example.fr', 'secret!12345');
        $event = $this->getEventRepository()->findOneByUuid(LoadEventData::EVENT_3_UUID);
        $eventUrl = sprintf('/evenements/%s/%s', LoadEventData::EVENT_3_UUID, $slug = $event->getSlug());

        $this->assertCount(0, $this->manager->getRepository(EventInvite::class)->findAll());

        // Initial form
        $crawler = $this->client->request(Request::METHOD_GET, $eventUrl.'/invitation');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->submit($crawler->filter('form[name=event_invitation]')->form([
            'event_invitation[message]' => 'Venez !',
            'event_invitation[guests][0]' => 'hugo.hamon@clichy-beach.com',
            'event_invitation[guests][1]' => 'jules.pietri@clichy-beach.com',
        ]));

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo($eventUrl.'/invitation/merci', $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->assertContains('Merci ! Vos 2 invitations ont bien été envoyées !', trim($crawler->filter('.event_invitation-result > p')->text()));

        // Invitation should have been saved
        $this->assertCount(1, $invitations = $this->manager->getRepository(EventInvite::class)->findAll());

        /** @var EventInvite $invite */
        $invite = $invitations[0];

        $this->assertSame('carl999@example.fr', $invite->getEmail());
        $this->assertSame('Carl Mirabeau', $invite->getFullName());
        $this->assertSame('hugo.hamon@clichy-beach.com', $invite->getGuests()[0]);
        $this->assertSame('jules.pietri@clichy-beach.com', $invite->getGuests()[1]);

        // Email should have been sent
        $this->assertCount(1, $messages = $this->getMailjetEmailRepository()->findMessages(EventInvitationMessage::class));
        $this->assertContains(str_replace('/', '\/', $eventUrl), $messages[0]->getRequestPayloadJson());
    }

    public function testAnonymousCanInviteToEvent()
    {
        $event = $this->getEventRepository()->findOneByUuid(LoadEventData::EVENT_3_UUID);
        $eventUrl = sprintf('/evenements/%s/%s', LoadEventData::EVENT_3_UUID, $slug = $event->getSlug());

        $this->assertCount(0, $this->manager->getRepository(EventInvite::class)->findAll());

        // Initial form
        $crawler = $this->client->request(Request::METHOD_GET, $eventUrl.'/invitation');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->submit($crawler->filter('form[name=event_invitation]')->form([
            'event_invitation[email]' => 'titouan@en-marche.fr',
            'event_invitation[firstName]' => 'Titouan',
            'event_invitation[lastName]' => 'Galopin',
            'event_invitation[message]' => 'Venez !',
            'event_invitation[guests][0]' => 'hugo.hamon@clichy-beach.com',
            'event_invitation[guests][1]' => 'jules.pietri@clichy-beach.com',
        ]));

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo($eventUrl.'/invitation/merci', $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->assertContains('Merci ! Vos 2 invitations ont bien été envoyées !', trim($crawler->filter('.event_invitation-result > p')->text()));

        // Invitation should have been saved
        $this->assertCount(1, $invitations = $this->manager->getRepository(EventInvite::class)->findAll());

        /** @var EventInvite $invite */
        $invite = $invitations[0];

        $this->assertSame('titouan@en-marche.fr', $invite->getEmail());
        $this->assertSame('Titouan Galopin', $invite->getFullName());
        $this->assertSame('hugo.hamon@clichy-beach.com', $invite->getGuests()[0]);
        $this->assertSame('jules.pietri@clichy-beach.com', $invite->getGuests()[1]);

        // Email should have been sent
        $this->assertCount(1, $messages = $this->getMailjetEmailRepository()->findMessages(EventInvitationMessage::class));
        $this->assertContains(str_replace('/', '\/', $eventUrl), $messages[0]->getRequestPayloadJson());
    }

    public function testInvitationSentWithoutRedirection()
    {
        $event = $this->getEventRepository()->findOneByUuid(LoadEventData::EVENT_1_UUID);

        $this->client->request(Request::METHOD_GET, sprintf('/evenements/%s/%s/invitation/merci', LoadEventData::EVENT_1_UUID, $event->getSlug()));

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
    }

    private function seeMessageSuccesfullyCreatedFlash(Crawler $crawler, ?string $message = null)
    {
        $flash = $crawler->filter('#notice-flashes');

        if ($message) {
            $this->assertSame($message, trim($flash->text()));
        }

        return 1 === count($flash);
    }

    private function seeMembersList(Crawler $crawler, int $count): bool
    {
        // Header row is part of the count
        return $count === count($crawler->filter('table > tr'));
    }

    protected function setUp()
    {
        parent::setUp();

        $this->init([
            LoadHomeBlockData::class,
            LoadAdherentData::class,
            LoadEventData::class,
        ]);

        $this->repository = $this->getEventRegistrationRepository();
    }

    protected function tearDown()
    {
        $this->kill();

        $this->repository = null;

        parent::tearDown();
    }
}
