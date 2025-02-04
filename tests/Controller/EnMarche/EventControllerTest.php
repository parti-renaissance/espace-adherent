<?php

namespace Tests\App\Controller\EnMarche;

use App\DataFixtures\ORM\LoadAdherentData;
use App\DataFixtures\ORM\LoadCommitteeEventData;
use App\DataFixtures\ORM\LoadEventCategoryData;
use App\Entity\Event\EventInvite;
use App\Entity\Renaissance\NewsletterSubscription;
use App\Mailer\Message\Renaissance\RenaissanceEventInvitationMessage;
use App\Repository\Email\EmailLogRepository;
use App\Repository\EventRegistrationRepository;
use Cake\Chronos\Chronos;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractEnMarcheWebTestCase;
use Tests\App\Controller\ControllerTestTrait;

#[Group('functional')]
#[Group('controller')]
class EventControllerTest extends AbstractEnMarcheWebTestCase
{
    use ControllerTestTrait;

    private ?EventRegistrationRepository $repository;
    private ?EmailLogRepository $emailRepository;

    public function testAnonymousUserCanRegisterToEvent()
    {
        Chronos::setTestNow('2018-05-18');

        $this->assertCount(0, $this->getRepository(NewsletterSubscription::class)->findAll());

        $crawler = $this->client->request(Request::METHOD_GET, '/');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $crawler = $this->client->click($crawler->selectLink('Rejoindre un comité')->link());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        self::assertSame('En Marche Paris 8', trim($crawler->filter('.search__results__meta > h2')->text()));

        $this->client->click($crawler->filter('.search__committee__box')->link());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        Chronos::setTestNow();
    }

    public function testRegisteredAdherentUserCanRegisterToEvent()
    {
        Chronos::setTestNow('2018-05-18');

        $this->authenticateAsAdherent($this->client, 'deputy@en-marche-dev.fr');
        $crawler = $this->client->request(Request::METHOD_GET, '/');

        $crawler = $this->client->click($crawler->selectLink('Rejoindre un comité')->link());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        self::assertSame('En Marche Paris 8', trim($crawler->filter('.search__results__meta > h2')->text()));

        $this->client->click($crawler->filter('.search__committee__box')->link());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        Chronos::setTestNow();
    }

    public function testCantRegisterToAFullEvent()
    {
        Chronos::setTestNow('2018-05-18');

        $this->authenticateAsAdherent($this->client, 'benjyd@aol.com');

        $eventUrl = '/evenements/'.self::getRelativeDate('2018-05-18', '+17 days').'-reunion-de-reflexion-marseillaise';
        $crawler = $this->client->request('GET', $eventUrl);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $headerText = $crawler->filter('.committee__event__header__cta')->text();
        $this->assertStringContainsString('1 inscrit', $headerText);
        $this->assertStringNotContainsString('JE VEUX PARTICIPER', $headerText);

        $this->client->request('GET', $eventUrl.'/inscription');

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->client->followRedirect();

        $crawler = $this->client->followRedirect();

        self::assertSame("L'événement est complet", $crawler->filter('.flash .flash__inner')->text());

        Chronos::setTestNow();
    }

    public function testAdherentCanInviteToEvent()
    {
        $this->authenticateAsAdherent($this->client, 'carl999@example.fr');
        $event = $this->getEventRepository()->findOneByUuid(LoadCommitteeEventData::EVENT_3_UUID);
        $eventUrl = \sprintf('/evenements/%s', $event->getSlug());

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

        $this->assertStringContainsString('Merci ! Vos 2 invitations ont bien été envoyées !', trim($crawler->filter('.event_invitation-result > p')->text()));

        // Invitation should have been saved
        $this->assertCount(1, $invitations = $this->manager->getRepository(EventInvite::class)->findAll());

        /** @var EventInvite $invite */
        $invite = $invitations[0];

        self::assertSame('carl999@example.fr', $invite->getEmail());
        self::assertSame('Carl Mirabeau', $invite->getFullName());
        self::assertSame('hugo.hamon@clichy-beach.com', $invite->getGuests()[0]);
        self::assertSame('jules.pietri@clichy-beach.com', $invite->getGuests()[1]);

        // Email should have been sent
        $this->assertCount(1, $messages = $this->getEmailRepository()->findMessages(RenaissanceEventInvitationMessage::class));
        $this->assertStringContainsString(str_replace('/', '\/', $eventUrl), $messages[0]->getRequestPayloadJson());
    }

    #[DataProvider('dataProviderNearbyEvents')]
    public function testAnonymousCanSeeThreeNearbyEvents(string $name, string $cityName)
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/evenements/2017-02-20-grand-meeting-de-paris');
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->assertStringContainsString('Les événements à proximité', $crawler->filter('.committee-event-nearby')->text());
        self::assertSame(3, $crawler->filter('.committee-event-nearby ul li')->count());
        $this->assertStringContainsString($name, $crawler->filter('.committee-event-nearby ul')->text());
        $this->assertStringContainsString($cityName, $crawler->filter('.committee-event-nearby ul')->text());
    }

    public static function dataProviderNearbyEvents(): iterable
    {
        yield ['Marche Parisienne', 'Paris 8ème'];
        yield ['Événement à Paris 2', 'Paris 8ème'];
        yield ['Événement à Paris 1', 'Paris 8ème'];
    }

    public function testAnonymousCanInviteToEvent()
    {
        $event = $this->getEventRepository()->findOneByUuid(LoadCommitteeEventData::EVENT_4_UUID);
        $eventUrl = \sprintf('/evenements/%s', $event->getSlug());

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

        $this->assertStringContainsString('Merci ! Vos 2 invitations ont bien été envoyées !', trim($crawler->filter('.event_invitation-result > p')->text()));

        // Invitation should have been saved
        $this->assertCount(1, $invitations = $this->manager->getRepository(EventInvite::class)->findAll());

        /** @var EventInvite $invite */
        $invite = $invitations[0];

        self::assertSame('titouan@en-marche.fr', $invite->getEmail());
        self::assertSame('Titouan Galopin', $invite->getFullName());
        self::assertSame('hugo.hamon@clichy-beach.com', $invite->getGuests()[0]);
        self::assertSame('jules.pietri@clichy-beach.com', $invite->getGuests()[1]);

        // Email should have been sent
        $this->assertCount(1, $messages = $this->getEmailRepository()->findMessages(RenaissanceEventInvitationMessage::class));
        $this->assertStringContainsString(str_replace('/', '\/', $eventUrl), $messages[0]->getRequestPayloadJson());
    }

    public function testInvitationSentWithoutRedirection()
    {
        $event = $this->getEventRepository()->findOneByUuid(LoadCommitteeEventData::EVENT_1_UUID);

        $this->client->request(Request::METHOD_GET, \sprintf('/evenements/%s/invitation/merci', $event->getSlug()));

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
    }

    public function testAttendConfirmationWithoutRegistration()
    {
        $event = $this->getEventRepository()->findOneByUuid(LoadCommitteeEventData::EVENT_1_UUID);

        $this->assertRedirectionEventNotPublishTest(\sprintf('/evenements/%s/confirmation', $event->getSlug()));
    }

    public function testAttendConfirmationWithWrongRegistration()
    {
        $event = $this->getEventRepository()->findOneByUuid(LoadCommitteeEventData::EVENT_1_UUID);

        $this->client->request(Request::METHOD_GET, \sprintf('/evenements/%s/confirmation', $event->getSlug()), [
            'registration' => 'wrong_uuid',
        ]);

        $this->assertStatusCode(Response::HTTP_BAD_REQUEST, $this->client);
    }

    public function testAttendConfirmationAsAnonymous()
    {
        $event = $this->getEventRepository()->findOneByUuid(LoadCommitteeEventData::EVENT_3_UUID);
        $registration = $this->getEventRegistrationRepository()->findAdherentRegistration(LoadCommitteeEventData::EVENT_3_UUID, LoadAdherentData::ADHERENT_7_UUID);

        $this->client->request(Request::METHOD_GET, \sprintf('/evenements/%s/confirmation', $event->getSlug()), [
            'registration' => $registration->getUuid()->toString(),
        ]);

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);
    }

    public function testAttendConfirmationAsAdherent()
    {
        $this->authenticateAsAdherent($this->client, 'francis.brioul@yahoo.com');

        $event = $this->getEventRepository()->findOneByUuid(LoadCommitteeEventData::EVENT_3_UUID);
        $registration = $this->getEventRegistrationRepository()->findAdherentRegistration(LoadCommitteeEventData::EVENT_3_UUID, LoadAdherentData::ADHERENT_7_UUID);

        $this->client->request(Request::METHOD_GET, \sprintf('/evenements/%s/confirmation', $event->getSlug()), [
            'registration' => $registration->getUuid()->toString(),
        ]);

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
    }

    public function testUnpublishedEventNotFound()
    {
        $event = $this->getEventRepository()->findOneByUuid(LoadCommitteeEventData::EVENT_13_UUID);
        $eventUrl = \sprintf('/evenements/%s', $event->getSlug());

        $this->assertRedirectionEventNotPublishTest($eventUrl);
    }

    public function testRedirectIfEventNotExist()
    {
        $this->assertRedirectionEventNotPublishTest('/evenements/2017-04-29-rassemblement-des-soutiens-regionaux-a-la-candidature-de-macron/inscription');
    }

    public function testRedirectionEventFromOldUrl()
    {
        $this->client->request(Request::METHOD_GET, '/evenements/'.LoadCommitteeEventData::EVENT_5_UUID.'/'.self::getRelativeDate('2018-05-18', '+17 days').'-reunion-de-reflexion-marseillaise');

        $this->assertClientIsRedirectedTo(
            '/evenements/'.self::getRelativeDate('2018-05-18', '+17 days').'-reunion-de-reflexion-marseillaise',
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
        Chronos::setTestNow('2018-05-18');

        $event = $this->getEventRepository()->findOneByUuid(LoadCommitteeEventData::EVENT_14_UUID);
        $eventUrl = \sprintf('/evenements/%s/inscription', $event->getSlug());
        $crawler = $this->client->request('GET', $eventUrl);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $link = $crawler->filter('.list__links.list__links--row.list__links--default li a')->eq(0)->attr('href');
        $needle = 'text=Meeting%20%2311%20de%20Brooklyn';

        $this->assertStringContainsString($needle, $link);

        Chronos::setTestNow();
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

        $this->assertNotContains('Catégorie masquée', $labels);
        self::assertSame($countCategories, $options->count());
        self::assertSame(4, $optgroup->count());
    }

    public function testAdherentCanUnregisterToEvent()
    {
        Chronos::setTestNow('2018-05-18');

        $this->authenticateAsAdherent($this->client, 'luciole1989@spambox.fr');

        $eventUrl = $this->getEventUrl();

        $crawler = $this->client->request(Request::METHOD_GET, $eventUrl);

        self::assertSame('1 inscrit', trim($crawler->filter('.committee-event-attendees')->text()));
        self::assertSame('Je ne peux plus participer', trim($crawler->filter('.unregister-event')->text()));

        $unregistrationButton = $this->client->getCrawler()->filter('.unregister-event');

        $this->client->request(Request::METHOD_POST, \sprintf('%s/desinscription', $this->getEventUrl()), [
            'token' => $unregistrationButton->attr('data-csrf-token'),
        ], [], ['HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $crawler = $this->client->request(Request::METHOD_GET, $eventUrl);

        self::assertSame('0 inscrit', trim($crawler->filter('.committee-event-attendees')->text()));
        self::assertSame('Je veux participer', trim($crawler->filter('.register-event')->text()));

        Chronos::setTestNow();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->getEventRegistrationRepository();
        $this->emailRepository = $this->getEmailRepository();
    }

    protected function tearDown(): void
    {
        $this->repository = null;
        $this->emailRepository = null;

        parent::tearDown();
    }

    protected function getEventUrl(): string
    {
        return '/evenements/'.self::getRelativeDate('2018-05-18', '+3 days').'-reunion-de-reflexion-parisienne';
    }

    private static function getRelativeDate(string $date, string $modifier, string $format = 'Y-m-d'): string
    {
        return (new \DateTime($date))->modify($modifier)->format($format);
    }
}
