<?php

namespace Tests\AppBundle\Controller\EnMarche;

use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\DataFixtures\ORM\LoadEventCategoryData;
use AppBundle\DataFixtures\ORM\LoadEventData;
use AppBundle\DataFixtures\ORM\LoadHomeBlockData;
use AppBundle\Entity\EventInvite;
use AppBundle\Entity\EventRegistration;
use AppBundle\Mailjet\Message\EventInvitationMessage;
use AppBundle\Mailjet\Message\EventRegistrationConfirmationMessage;
use AppBundle\Repository\EventRegistrationRepository;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\Controller\ControllerTestTrait;
use Tests\AppBundle\MysqlWebTestCase;

/**
 * @group functional
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

        $crawler = $this->client->click($crawler->selectLink('Retour')->link());

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

        $crawler = $this->client->click($crawler->selectLink('Retour')->link());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame('2 / 50 inscrits', trim($crawler->filter('.committee-event-attendees')->text()));

        $this->client->click($crawler->selectLink('Mes événements')->link());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertContains('Réunion de réflexion parisienne', $this->client->getResponse()->getContent());
    }

    public function testCantRegisterToAFullEvent()
    {
        $this->authenticateAsAdherent($this->client, 'benjyd@aol.com', 'HipHipHip');

        $eventUrl = '/evenements/'.date('Y-m-d', strtotime('+17 days')).'-reunion-de-reflexion-marseillaise';
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

    public function testAdherentCanInviteToEvent()
    {
        $this->authenticateAsAdherent($this->client, 'carl999@example.fr', 'secret!12345');
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
        $eventUrl = sprintf('/evenements/%s', $slug = $event->getSlug());

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
        $this->authenticateAsAdherent($this->client, 'francis.brioul@yahoo.com', 'Champion20');

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

    private function seeMessageSuccesfullyCreatedFlash(Crawler $crawler, ?string $message = null)
    {
        $flash = $crawler->filter('#notice-flashes');

        if ($message) {
            $this->assertSame($message, trim($flash->text()));
        }

        return 1 === count($flash);
    }

    public function testRedirectIfEventNotExist()
    {
        $this->assertRedirectionEventNotPublishTest('/evenements/0cf668bc-f3b4-449d-8cfe-12cdb1ae12aa/2017-04-29-rassemblement-des-soutiens-regionaux-a-la-candidature-de-macron/inscription');
    }

    private function assertRedirectionEventNotPublishTest($url)
    {
        $this->client->request(Request::METHOD_GET, '/evenements/0cf668bc-f3b4-449d-8cfe-12cdb1ae12aa/2017-04-29-rassemblement-des-soutiens-regionaux-a-la-candidature-de-macron/inscription');

        $this->assertStatusCode(Response::HTTP_MOVED_PERMANENTLY, $this->client);

        $this->assertClientIsRedirectedTo('/evenements', $this->client);
        $this->client->followRedirect();

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
    }

    public function testEventWithSpecialCharInTitle()
    {
        $this->authenticateAsAdherent($this->client, 'benjyd@aol.com', 'HipHipHip');

        $event = $this->getEventRepository()->findOneByUuid(LoadEventData::EVENT_14_UUID);
        $eventUrl = sprintf('/evenements/%s/%s/inscription', LoadEventData::EVENT_14_UUID, $event->getSlug());
        $crawler = $this->client->request('GET', $eventUrl);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $link = $crawler->filter('.list__links.list__links--row.list__links--default li a')->eq(0)->attr('href');
        $needle = 'text=Meeting%20%2311%20de%20Brooklyn';
        $this->assertContains($needle, $link);
    }

    protected function setUp()
    {
        parent::setUp();

        $this->init([
            LoadAdherentData::class,
            LoadEventCategoryData::class,
            LoadEventData::class,
            LoadHomeBlockData::class,
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
