<?php

namespace Tests\AppBundle\Controller\EnMarche;

use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\DataFixtures\ORM\LoadCitizenActionData;
use AppBundle\Entity\CitizenAction;
use AppBundle\Entity\EventRegistration;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group functional
 * @group citizenAction
 */
class CitizenActionControllerTest extends AbstractEventControllerTest
{
    /**
     * @var \AppBundle\Repository\EventRegistrationRepository
     */
    private $repository;

    public function testAnonymousUserCanRegisterToCitizenAction()
    {
        $registrations = $this->getEventRegistrationRepository()->findAll();
        $initialCount = \count($registrations);

        $eventUrl = '/action-citoyenne/'.date('Y-m-d', strtotime('tomorrow')).'-projet-citoyen-3';
        $crawler = $this->client->request('GET', $eventUrl);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame(1, $crawler->filter('.citizen_action header a:contains("S\'inscrire")')->count());

        $crawler = $this->client->click($crawler->selectLink('S\'inscrire')->link());

        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $this->client->click($crawler->selectButton('Je m\'inscris')->form([
            'event_registration[firstName]' => 'Anonymous',
            'event_registration[lastName]' => 'Guest',
            'event_registration[emailAddress]' => 'anonymous.guest@exemple.org',
            'event_registration[acceptTerms]' => '1',
        ]));

        /** @var EventRegistration[] $registrations */
        $registrations = $this->getEventRegistrationRepository()->findAll();
        $lastUuid = end($registrations)->getUuid();

        $this->assertCount($initialCount + 1, $registrations);
        $this->assertClientIsRedirectedTo("$eventUrl/confirmation?registration=".$lastUuid, $this->client);
    }

    public function testRegisteredAdherentUserCanRegisterToCitizenAction()
    {
        $this->authenticateAsAdherent($this->client, 'benjyd@aol.com');

        $eventUrl = '/action-citoyenne/'.date('Y-m-d', strtotime('tomorrow')).'-projet-citoyen-3';
        $crawler = $this->client->request('GET', $eventUrl);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame('1 inscrit', trim($crawler->filter('#members h3')->text()));

        $this->client->click($crawler->selectLink("S'inscrire")->link());

        $this->client->followRedirect();

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertInstanceOf(EventRegistration::class, $this->getEventRegistrationRepository()->findGuestRegistration(LoadCitizenActionData::CITIZEN_ACTION_3_UUID, 'benjyd@aol.com'));

        $crawler = $this->client->followRedirect();

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertTrue($this->seeFlashMessage($crawler, 'Votre inscription est confirmée.'));
        $this->assertContains('Votre participation est bien enregistrée !', $crawler->filter('.committee-event-registration-confirmation p')->text());

        $crawler = $this->client->click($crawler->selectLink('Retour')->link());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        self::assertSame('2 inscrits', trim($crawler->filter('#members h3')->text()));
    }

    public function testExportIcalAction(): void
    {
        $uuid = LoadCitizenActionData::CITIZEN_ACTION_3_UUID;
        /** @var CitizenAction $citizenAction */
        $citizenAction = $this->getCitizenActionRepository()->findOneBy(['uuid' => $uuid]);

        $this->client->request(Request::METHOD_GET, sprintf('/action-citoyenne/%s/ical', $citizenAction->getSlug()));

        $this->isSuccessful($response = $this->client->getResponse());
        self::assertSame(sprintf('attachment; filename=%s-projet-citoyen-3.ics', $citizenAction->getFinishAt()->format('Y-m-d')), $response->headers->get('Content-Disposition'));
        self::assertSame('text/calendar; charset=UTF-8', $response->headers->get('Content-Type'));

        $beginAt = preg_quote($citizenAction->getLocalBeginAt()->format('Ymd\THis'), '/');
        $finishAt = preg_quote($citizenAction->getLocalFinishAt()->format('Ymd\THis'), '/');
        $uuid = preg_quote($uuid, '/');
        $icalRegex = <<<CONTENT
BEGIN\:VCALENDAR
VERSION\:2\.0
PRODID\:\-\/\/Sabre\/\/Sabre VObject 4\.1\.6\/\/EN
CALSCALE\:GREGORIAN
ORGANIZER\:Jacques PICARD
BEGIN\:VEVENT
UID\:$uuid
DTSTAMP\:\\d{8}T\\d{6}Z
SUMMARY\:Projet citoyen #3
DESCRIPTION\:Un troisième projet citoyen
DTSTART;TZID=Europe\/Paris\:$beginAt
DTEND;TZID=Europe\/Paris\:$finishAt
LOCATION\:16 rue de la Paix\\\\, 75008 Paris 8e
END\:VEVENT
END\:VCALENDAR
CONTENT;
        $icalRegex = str_replace("\n", "\r\n", $icalRegex); // Returned content contains CRLF

        $this->assertRegExp(sprintf('/%s/', $icalRegex), $response->getContent());
    }

    public function testUnregistrationSuccessful(): void
    {
        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');

        $uuid = LoadCitizenActionData::CITIZEN_ACTION_3_UUID;
        /** @var CitizenAction $citizenAction */
        $citizenAction = $this->getCitizenActionRepository()->findOneBy(['uuid' => $uuid]);

        $this->client->request(Request::METHOD_GET, sprintf('/action-citoyenne/%s', $citizenAction->getSlug()));

        $unregistrationButton = $this->client->getCrawler()->filter('#citizen_action-unregistration');

        $this->assertSame('Se désinscrire', trim($unregistrationButton->text()));
        $this->assertInstanceOf(EventRegistration::class, $this->getEventRegistrationRepository()->findAdherentRegistration($uuid, LoadAdherentData::ADHERENT_3_UUID));

        $this->client->request(Request::METHOD_POST, sprintf('/action-citoyenne/%s/desinscription', $citizenAction->getSlug()), [
            'token' => $unregistrationButton->attr('data-csrf-token'),
        ], [], ['HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->request(Request::METHOD_GET, sprintf('/action-citoyenne/%s', $citizenAction->getSlug()));

        $this->assertSame('S\'inscrire', $this->client->getCrawler()->filter('a.newbtn--orange')->text());
        $this->assertNull($this->getEventRegistrationRepository()->findAdherentRegistration($uuid, LoadAdherentData::ADHERENT_3_UUID));
    }

    public function testUnregistrationFailed(): void
    {
        $this->markTestSkipped('Need to fix different token problem');

        $this->authenticateAsAdherent($this->client, 'carl999@example.fr');

        $uuid = LoadCitizenActionData::CITIZEN_ACTION_4_UUID;
        /** @var CitizenAction $citizenAction */
        $citizenAction = $this->getRepository(CitizenAction::class)->findOneBy(['uuid' => $uuid]);

        $crawler = $this->client->request(Request::METHOD_GET, sprintf('/action-citoyenne/%s', $citizenAction->getSlug()));

        $this->assertSame('S\'inscrire', $crawler->filter('a.newbtn--orange')->text());
        $this->assertNull($this->getEventRegistrationRepository()->findAdherentRegistration($uuid, LoadAdherentData::ADHERENT_2_UUID));

        $csrfToken = $this->getContainer()->get('security.csrf.token_manager')->getToken('event.unregistration');

        $this->client->request(Request::METHOD_POST, sprintf('/action-citoyenne/%s/desinscription', $citizenAction->getSlug()), [
            'token' => $csrfToken,
        ], [], ['HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);

        $this->assertResponseStatusCode(Response::HTTP_NOT_FOUND, $this->client->getResponse());
    }

    public function testCitizenProjectAdministratorCanSeeParticipants()
    {
        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');

        $uuid = LoadCitizenActionData::CITIZEN_ACTION_4_UUID;
        /** @var CitizenAction $citizenAction */
        $citizenAction = $this->getCitizenActionRepository()->findOneBy(['uuid' => $uuid]);

        $crawler = $this->client->request(Request::METHOD_GET, sprintf('/action-citoyenne/%s/participants', $citizenAction->getSlug()));

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->assertCount(6, $crawler->filter('table.committee__members__list thead th'));
        $this->assertCount(5, $crawler->filter('table.committee__members__list tbody tr'));
        $this->assertCount(1, $crawler->filter('table.committee__members__list tbody tr.committee__members__list__host td.member-first-name:contains("Jacques")'));
        $this->assertCount(1, $crawler->filter('table.committee__members__list tbody tr.committee__members__list__host td.member-last-name:contains("PICARD")'));
        $this->assertCount(1, $crawler->filter('table.committee__members__list td.member-first-name:contains("Gisele")'));
        $this->assertCount(1, $crawler->filter('table.committee__members__list td.member-last-name:contains("BERTHOUX")'));
        $this->assertCount(1, $crawler->filter('table.committee__members__list td.member-first-name:contains("Lucie")'));
        $this->assertCount(1, $crawler->filter('table.committee__members__list td.member-last-name:contains("OLIVERA")'));
        $this->assertCount(1, $crawler->filter('table.committee__members__list td.member-first-name:contains("Marie")'));
        $this->assertCount(1, $crawler->filter('table.committee__members__list td.member-last-name:contains("CLAIRE")'));
        $this->assertCount(1, $crawler->filter('table.committee__members__list td.member-first-name:contains("Pierre")'));
        $this->assertCount(1, $crawler->filter('table.committee__members__list td.member-last-name:contains("FRANCE")'));
    }

    public function testAdherentCanSeeParticipants()
    {
        $this->authenticateAsAdherent($this->client, 'carl999@example.fr');

        $uuid = LoadCitizenActionData::CITIZEN_ACTION_4_UUID;
        /** @var CitizenAction $citizenAction */
        $citizenAction = $this->getCitizenActionRepository()->findOneBy(['uuid' => $uuid]);

        $crawler = $this->client->request(Request::METHOD_GET, sprintf('/action-citoyenne/%s/participants', $citizenAction->getSlug()));

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->assertCount(1, $crawler->filter('table.committee__members__list thead th'));
        $this->assertCount(5, $crawler->filter('table.committee__members__list tbody tr'));
        $this->assertCount(1, $crawler->filter('table.committee__members__list tbody tr.committee__members__list__host td.member-first-name:contains("Jacques")'));
        $this->assertCount(1, $crawler->filter('table.committee__members__list td.member-first-name:contains("Gisele")'));
        $this->assertCount(1, $crawler->filter('table.committee__members__list td.member-first-name:contains("Lucie")'));
        $this->assertCount(1, $crawler->filter('table.committee__members__list td.member-first-name:contains("Marie")'));
        $this->assertCount(1, $crawler->filter('table.committee__members__list td.member-first-name:contains("Pierre")'));
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
        return '/action-citoyenne/'.date('Y-m-d', strtotime('+1 day')).'-projet-citoyen-3';
    }
}
