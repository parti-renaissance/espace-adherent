<?php

namespace Tests\AppBundle\Controller\EnMarche;

use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\DataFixtures\ORM\LoadEventCategoryData;
use AppBundle\DataFixtures\ORM\LoadEventData;
use AppBundle\Entity\EventInvite;
use AppBundle\Entity\EventRegistration;
use AppBundle\Mailer\Message\EventInvitationMessage;
use AppBundle\Mailer\Message\EventRegistrationConfirmationMessage;
use AppBundle\Repository\EventRegistrationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group functional
 * @group controller
 */
class EventControllerTest extends AbstractEventControllerTest
{
    /** @var EventRegistrationRepository */
    private $repository;

    public function testAnonymousUserCanRegisterToEvent()
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $crawler = $this->client->click($crawler->selectLink('Rejoindre un comité')->link());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        self::assertSame('En Marche Paris 8', trim($crawler->filter('.search__results__meta > h2')->text()));

        $crawler = $this->client->click($crawler->filter('.search__committee__box')->link());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        self::assertSame('1 inscrit', trim($crawler->filter('.committee-event-attendees')->text()));

        $crawler = $this->client->click($crawler->filter('.committee-event-more')->link());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $crawler = $this->client->click($crawler->selectLink('Je veux participer')->link());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertEmpty($crawler->filter('#field-first-name > input[type="text"]')->attr('value'));
        $this->assertEmpty($crawler->filter('#field-last-name > input[type="text"]')->attr('value'));
        $this->assertEmpty($crawler->filter('#field-email-address > input[type="email"]')->attr('value'));
        self::assertSame(1, $crawler->filter('#field-accept-terms')->count());
        self::assertSame(1, $crawler->filter('#field-newsletter-subscriber')->count());

        $crawler = $this->client->submit($crawler->selectButton("Je m'inscris")->form());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        self::assertSame(3, $crawler->filter('.form__errors')->count());
        self::assertSame('Cette valeur ne doit pas être vide.', $crawler->filter('#field-first-name .form__errors > li')->text());
        self::assertSame('Cette valeur ne doit pas être vide.', $crawler->filter('#field-last-name .form__errors > li')->text());
        self::assertSame('Cette valeur ne doit pas être vide.', $crawler->filter('#field-email-address .form__errors > li')->text());

        $this->client->submit($crawler->selectButton("Je m'inscris")->form([
            'event_registration' => [
                'firstName' => 'Pauline',
                'emailAddress' => 'paupau75@example.org',
                'lastName' => '75001',
                'newsletterSubscriber' => true,
                'acceptTerms' => true,
            ],
        ]));

        $this->assertInstanceOf(EventRegistration::class, $this->repository->findGuestRegistration(LoadEventData::EVENT_1_UUID, 'paupau75@example.org'));
        $this->assertCount(1, $this->getEmailRepository()->findRecipientMessages(EventRegistrationConfirmationMessage::class, 'paupau75@example.org'));

        $crawler = $this->client->followRedirect();

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertTrue($this->seeFlashMessage($crawler, "Votre inscription à l'événement est confirmée."));
        $this->assertContains('Votre participation est bien enregistrée !', $crawler->filter('.committee-event-registration-confirmation p')->text());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
    }

    public function testRegisteredAdherentUserCanRegisterToEvent()
    {
        $this->authenticateAsAdherent($this->client, 'deputy@en-marche-dev.fr');
        $crawler = $this->client->request(Request::METHOD_GET, '/');

        $crawler = $this->client->click($crawler->selectLink('Rejoindre un comité')->link());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        self::assertSame('En Marche Paris 8', trim($crawler->filter('.search__results__meta > h2')->text()));

        $crawler = $this->client->click($crawler->filter('.search__committee__box')->link());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        self::assertSame('1 inscrit', trim($crawler->filter('.committee-event-attendees')->text()));

        $crawler = $this->client->click($crawler->filter('.committee-event-more')->link());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->click($crawler->selectLink('Je veux participer')->link());

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->client->followRedirect();

        $this->assertInstanceOf(EventRegistration::class, $this->repository->findGuestRegistration(LoadEventData::EVENT_1_UUID, 'deputy@en-marche-dev.fr'));
        $this->assertCount(1, $this->getEmailRepository()->findRecipientMessages(EventRegistrationConfirmationMessage::class, 'deputy@en-marche-dev.fr'));

        $crawler = $this->client->followRedirect();

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertTrue($this->seeFlashMessage($crawler, "Votre inscription à l'événement est confirmée."));
        $this->assertContains('Votre participation est bien enregistrée !', $crawler->filter('.committee-event-registration-confirmation p')->text());

        $crawler = $this->client->click($crawler->selectLink('Retour')->link());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        self::assertSame('2 inscrits', trim($crawler->filter('.committee-event-attendees')->text()));

        $this->client->click($crawler->selectLink('Mes activités')->link());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertContains('Réunion de réflexion parisienne', $this->client->getResponse()->getContent());
    }

    public function testCantRegisterToAFullEvent()
    {
        $this->authenticateAsAdherent($this->client, 'benjyd@aol.com');

        $eventUrl = '/evenements/'.date('Y-m-d', strtotime('+17 days')).'-reunion-de-reflexion-marseillaise';
        $crawler = $this->client->request('GET', $eventUrl);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $headerText = $crawler->filter('.committee__event__header__cta')->text();
        $this->assertContains('1 inscrit', $headerText);
        $this->assertNotContains('JE VEUX PARTICIPER', $headerText);

        $this->client->request('GET', $eventUrl.'/inscription');

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->client->followRedirect();

        $crawler = $this->client->followRedirect();

        self::assertSame("L'événement est complet", $crawler->filter('.flash .flash__inner')->text());
    }

    public function testAdherentCanInviteToEvent()
    {
        $this->authenticateAsAdherent($this->client, 'carl999@example.fr');
        $event = $this->getEventRepository()->findOneByUuid(LoadEventData::EVENT_3_UUID);
        $eventUrl = sprintf('/evenements/%s', $slug = $event->getSlug());

        $this->assertCount(0, $this->manager->getRepository(EventInvite::class)->findAll());

        // Initial form
        $crawler = $this->client->request(Request::METHOD_GET, $eventUrl.'/invitation');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->submit($crawler->filter('form[name=event_invitation]')->form([
            'event_invitation[message]' => 'Venez !',
            'event_invitation[guests][0]' => 'hugo.hamon@clichy-beach.com',
            'event_invitation[guests][1]' => 'jules.pietri@clichy-beach.com',
            'g-recaptcha-response' => 'foobar',
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

        self::assertSame('carl999@example.fr', $invite->getEmail());
        self::assertSame('Carl Mirabeau', $invite->getFullName());
        self::assertSame('hugo.hamon@clichy-beach.com', $invite->getGuests()[0]);
        self::assertSame('jules.pietri@clichy-beach.com', $invite->getGuests()[1]);

        // Email should have been sent
        $this->assertCount(1, $messages = $this->getEmailRepository()->findMessages(EventInvitationMessage::class));
        $this->assertContains(str_replace('/', '\/', $eventUrl), $messages[0]->getRequestPayloadJson());
    }

    /**
     * @dataProvider dataProviderNearbyEvents
     */
    public function testAnonymousCanSeeThreeNearbyEvents(string $name, string $cityName)
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/evenements/2017-02-20-grand-meeting-de-paris');
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->assertContains('Les événements à proximité', $crawler->filter('.committee-event-nearby')->text());
        self::assertSame(3, $crawler->filter('.committee-event-nearby ul li')->count());
        $this->assertContains($name, $crawler->filter('.committee-event-nearby ul')->text());
        $this->assertContains($cityName, $crawler->filter('.committee-event-nearby ul')->text());
    }

    public function dataProviderNearbyEvents(): iterable
    {
        yield ['Marche Parisienne', 'Paris 8e'];
        yield ['Événement à Paris 2', 'Paris 8e'];
        yield ['Événement à Paris 1', 'Paris 8e'];
    }

    public function testAnonymousCanInviteToEvent()
    {
        $event = $this->getEventRepository()->findOneByUuid(LoadEventData::EVENT_3_UUID);
        $eventUrl = sprintf('/evenements/%s', $slug = $event->getSlug());

        $this->assertCount(0, $this->manager->getRepository(EventInvite::class)->findAll());

        // Initial form
        $crawler = $this->client->request(Request::METHOD_GET, $eventUrl.'/invitation');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->submit($crawler->filter('form[name=event_invitation]')->form([
            'event_invitation[email]' => 'titouan@en-marche.fr',
            'event_invitation[firstName]' => 'Titouan',
            'event_invitation[lastName]' => 'Galopin',
            'event_invitation[message]' => '',
            'event_invitation[guests][0]' => 'hugo.hamon@clichy-beach.com',
            'event_invitation[guests][1]' => 'jules.pietri@clichy-beach.com',
            'g-recaptcha-response' => 'foobar',
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

        self::assertSame('titouan@en-marche.fr', $invite->getEmail());
        self::assertSame('Titouan Galopin', $invite->getFullName());
        self::assertSame('hugo.hamon@clichy-beach.com', $invite->getGuests()[0]);
        self::assertSame('jules.pietri@clichy-beach.com', $invite->getGuests()[1]);

        // Email should have been sent
        $this->assertCount(1, $messages = $this->getEmailRepository()->findMessages(EventInvitationMessage::class));
        $this->assertContains(str_replace('/', '\/', $eventUrl), $messages[0]->getRequestPayloadJson());
    }

    public function testInvitationSentWithoutRedirection()
    {
        $event = $this->getEventRepository()->findOneByUuid(LoadEventData::EVENT_1_UUID);

        $this->client->request(Request::METHOD_GET, sprintf('/evenements/%s/invitation/merci', $event->getSlug()));

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
    }

    public function testAttendConfirmationWithoutRegistration()
    {
        $event = $this->getEventRepository()->findOneByUuid(LoadEventData::EVENT_1_UUID);

        $this->assertRedirectionEventNotPublishTest(sprintf('/evenements/%s/confirmation', $event->getSlug()));
    }

    public function testAttendConfirmationWithWrongRegistration()
    {
        $event = $this->getEventRepository()->findOneByUuid(LoadEventData::EVENT_1_UUID);

        $this->client->request(Request::METHOD_GET, sprintf('/evenements/%s/confirmation', $event->getSlug()), [
            'registration' => 'wrong_uuid',
        ]);

        $this->assertStatusCode(Response::HTTP_BAD_REQUEST, $this->client);
    }

    public function testAttendConfirmationAsAnonymous()
    {
        $event = $this->getEventRepository()->findOneByUuid(LoadEventData::EVENT_3_UUID);
        $registration = $this->getEventRegistrationRepository()->findAdherentRegistration(LoadEventData::EVENT_3_UUID, LoadAdherentData::ADHERENT_7_UUID);

        $this->client->request(Request::METHOD_GET, sprintf('/evenements/%s/confirmation', $event->getSlug()), [
            'registration' => $registration->getUuid()->toString(),
        ]);

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);
    }

    public function testAttendConfirmationAsAdherent()
    {
        $this->authenticateAsAdherent($this->client, 'francis.brioul@yahoo.com');

        $event = $this->getEventRepository()->findOneByUuid(LoadEventData::EVENT_3_UUID);
        $registration = $this->getEventRegistrationRepository()->findAdherentRegistration(LoadEventData::EVENT_3_UUID, LoadAdherentData::ADHERENT_7_UUID);

        $this->client->request(Request::METHOD_GET, sprintf('/evenements/%s/confirmation', $event->getSlug()), [
            'registration' => $registration->getUuid()->toString(),
        ]);

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
    }

    public function testUnpublishedEventNotFound()
    {
        $event = $this->getEventRepository()->findOneByUuid(LoadEventData::EVENT_13_UUID);
        $eventUrl = sprintf('/evenements/%s', $event->getSlug());

        $this->assertRedirectionEventNotPublishTest($eventUrl);
    }

    public function testRedirectIfEventNotExist()
    {
        $this->assertRedirectionEventNotPublishTest('/evenements/2017-04-29-rassemblement-des-soutiens-regionaux-a-la-candidature-de-macron/inscription');
    }

    public function testRedirectionEventFromOldUrl()
    {
        $this->client->request(Request::METHOD_GET, '/evenements/'.LoadEventData::EVENT_5_UUID.'/'.date('Y-m-d', strtotime('+17 days')).'-reunion-de-reflexion-marseillaise');

        $this->assertClientIsRedirectedTo(
            '/evenements/'.date('Y-m-d', strtotime('+17 days')).'-reunion-de-reflexion-marseillaise',
            $this->client,
            false,
            true
        );

        $this->client->followRedirect();

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
    }

    private function assertRedirectionEventNotPublishTest(string $url): void
    {
        $this->client->request(Request::METHOD_GET, $url);

        $this->assertClientIsRedirectedTo('/evenements', $this->client, false, true);

        $this->client->followRedirect();

        $this->isSuccessful($this->client->getResponse());
    }

    public function testEventWithSpecialCharInTitle()
    {
        $event = $this->getEventRepository()->findOneByUuid(LoadEventData::EVENT_14_UUID);
        $eventUrl = sprintf('/evenements/%s/inscription', $event->getSlug());
        $crawler = $this->client->request('GET', $eventUrl);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $link = $crawler->filter('.list__links.list__links--row.list__links--default li a')->eq(0)->attr('href');
        $needle = 'text=Meeting%20%2311%20de%20Brooklyn';

        $this->assertContains($needle, $link);
    }

    public function testSearchCategoryForm()
    {
        $crawler = $this->client->request('GET', '/evenements');
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $options = $crawler->filter('.search__bar__options__types option');
        $optgroup = $crawler->filter('.search__bar__options__types optgroup');
        $array = $options->getIterator();
        $labels = [];
        foreach ($array as $element) {
            /* @var \DOMElement $element */
            $labels[] = $element->textContent;
        }

        $countCategories = \count(LoadEventCategoryData::LEGACY_EVENT_CATEGORIES);
        $countCategories += \count(LoadEventCategoryData::LEGACY_EVENT_CATEGORIES_GROUPED);

        ++$countCategories; // add citizen_action
        $this->assertNotContains('Catégorie masquée', $labels);
        self::assertSame($countCategories, $options->count());
        self::assertSame(3, $optgroup->count());
    }

    public function testAdherentCanUnregisterToEvent()
    {
        $this->authenticateAsAdherent($this->client, 'luciole1989@spambox.fr');

        $eventUrl = $this->getEventUrl();

        $crawler = $this->client->request(Request::METHOD_GET, $eventUrl);

        self::assertSame('1 inscrit', trim($crawler->filter('.committee-event-attendees')->text()));
        self::assertSame('Je ne peux plus participer', trim($crawler->filter('.unregister-event')->text()));

        $unregistrationButton = $this->client->getCrawler()->filter('.unregister-event');

        $this->client->request(Request::METHOD_POST, sprintf('%s/desinscription', $this->getEventUrl()), [
            'token' => $unregistrationButton->attr('data-csrf-token'),
        ], [], ['HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $crawler = $this->client->request(Request::METHOD_GET, $eventUrl);

        self::assertSame('0 inscrit', trim($crawler->filter('.committee-event-attendees')->text()));
        self::assertSame('Je veux participer', trim($crawler->filter('.register-event')->text()));
    }

    protected function setUp()
    {
        parent::setUp();

        $this->init();

        $this->repository = $this->getEventRegistrationRepository();
    }

    protected function tearDown()
    {
        $this->kill();

        $this->repository = null;

        parent::tearDown();
    }

    protected function getEventUrl(): string
    {
        return '/evenements/'.date('Y-m-d', strtotime('+3 days')).'-reunion-de-reflexion-parisienne';
    }
}
