<?php

namespace Tests\AppBundle\Controller\EnMarche;

use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\DataFixtures\ORM\LoadCitizenActionData;
use AppBundle\Entity\CitizenAction;
use AppBundle\Entity\EventRegistration;
use AppBundle\Mailer\Message\CitizenActionRegistrationConfirmationMessage;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\Controller\ControllerTestTrait;
use Tests\AppBundle\MysqlWebTestCase;

/**
 * @group functional
 * @group citizenAction
 */
class CitizenActionControllerTest extends MysqlWebTestCase
{
    use ControllerTestTrait;

    public function testAnonymousUserCanRegisterToCitizenAction()
    {
        $registrations = $this->getEventRegistrationRepository()->findAll();
        $initialCount = count($registrations);

        $eventUrl = '/action-citoyenne/'.date('Y-m-d', strtotime('tomorrow')).'-projet-citoyen-3';
        $crawler = $this->client->request('GET', $eventUrl);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame('1 inscrit', trim($crawler->filter('#members h3')->text()));
        $this->assertSame(0, $crawler->filter('.citizen_action header a:contains("S\'inscrire")')->count());

        $crawler = $this->client->request('GET', "$eventUrl/inscription");

        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $this->client->click($crawler->selectButton("Je m'inscris")->form([
            'event_registration[firstName]' => 'Anonymous',
            'event_registration[lastName]' => 'Guest',
            'event_registration[emailAddress]' => 'anonymous.guest@exemple.org',
            'event_registration[acceptTerms]' => '1',
        ]));

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

        $crawler = $this->client->click($crawler->selectLink("S'inscrire")->link());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame('Benjamin', $crawler->filter('#field-first-name > input[type="text"]')->attr('value'));
        $this->assertSame('Duroc', $crawler->filter('#field-last-name > input[type="text"]')->attr('value'));
        $this->assertSame('benjyd@aol.com', $crawler->filter('#field-email-address > input[type="email"]')->attr('value'));
        $this->assertSame(1, $crawler->filter('#field-accept-terms')->count());
        // Adherent is already subscribed to mails
        $this->assertSame(0, $crawler->filter('#field-newsletter-subscriber')->count());

        $this->client->submit($crawler->selectButton("Je m'inscris")->form());

        $this->assertInstanceOf(EventRegistration::class, $this->getEventRegistrationRepository()->findGuestRegistration(LoadCitizenActionData::CITIZEN_ACTION_3_UUID, 'benjyd@aol.com'));
        $this->assertCount(1, $this->getEmailRepository()->findRecipientMessages(CitizenActionRegistrationConfirmationMessage::class, 'benjyd@aol.com'));

        $crawler = $this->client->followRedirect();

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertTrue($this->seeFlashMessage($crawler, 'Votre inscription est confirmée.'));
        $this->assertContains('Votre participation est bien enregistrée !', $crawler->filter('.committee-event-registration-confirmation p')->text());

        $crawler = $this->client->click($crawler->selectLink('Retour')->link());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame('2 inscrits', trim($crawler->filter('#members h3')->text()));
    }

    public function testExportIcalAction(): void
    {
        $uuid = LoadCitizenActionData::CITIZEN_ACTION_3_UUID;
        /** @var CitizenAction $citizenAction */
        $citizenAction = $this->getCitizenActionRepository()->findOneBy(['uuid' => $uuid]);

        $this->client->request(Request::METHOD_GET, sprintf('/action-citoyenne/%s/ical', $citizenAction->getSlug()));

        $this->isSuccessful($response = $this->client->getResponse());
        $this->assertSame(sprintf('attachment; filename=%s-projet-citoyen-3.ics', $citizenAction->getFinishAt()->format('Y-m-d')), $response->headers->get('Content-Disposition'));
        $this->assertSame('text/calendar; charset=UTF-8', $response->headers->get('Content-Type'));

        $beginAt = preg_quote($citizenAction->getBeginAt()->format('Ymd\THis'), '/');
        $finishAt = preg_quote($citizenAction->getFinishAt()->format('Ymd\THis'), '/');
        $uuid = preg_quote($uuid, '/');
        $icalRegex = <<<CONTENT
BEGIN\:VCALENDAR
VERSION\:2\.0
PRODID\:\-\/\/Sabre\/\/Sabre VObject 4\.1\.6\/\/EN
CALSCALE\:GREGORIAN
ORGANIZER\:CN\="Jacques PICARD"\\\\;mailto\:jacques\.picard@en\-marche\.fr
BEGIN\:VEVENT
UID\:$uuid
DTSTAMP\:\\d{8}T\\d{6}Z
SUMMARY\:Projet citoyen #3
DESCRIPTION\:Un troisième projet citoyen
DTSTART\:$beginAt
DTEND\:$finishAt
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
        ]);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->request(Request::METHOD_GET, sprintf('/action-citoyenne/%s', $citizenAction->getSlug()));

        $this->assertSame('S\'inscrire', $this->client->getCrawler()->filter('a.newbtn--orange')->text());
        $this->assertNull($this->getEventRegistrationRepository()->findAdherentRegistration($uuid, LoadAdherentData::ADHERENT_3_UUID));
    }

    public function testUnregistrationFailed(): void
    {
        $this->markTestSkipped('Need to fix different token problem');

        $this->authenticateAsAdherent($this->client, 'carl999@example.fr');

        $this->client->disableReboot();
        $uuid = LoadCitizenActionData::CITIZEN_ACTION_4_UUID;
        /** @var CitizenAction $citizenAction */
        $citizenAction = $this->getRepository(CitizenAction::class)->findOneBy(['uuid' => $uuid]);

        $this->client->request(Request::METHOD_GET, sprintf('/action-citoyenne/%s', $citizenAction->getSlug()));

        $this->assertSame('S\'inscrire', $this->client->getCrawler()->filter('a.newbtn--orange')->text());
        $this->assertNull($this->getEventRegistrationRepository()->findAdherentRegistration($uuid, LoadAdherentData::ADHERENT_2_UUID));

        $csrfToken = $this->container->get('security.csrf.token_manager')->getToken('citizen_action.unregistration');
        $this->client->request(Request::METHOD_POST, sprintf('/action-citoyenne/%s/desinscription', $citizenAction->getSlug()), [
            'token' => $csrfToken,
        ]);

        $this->assertResponseStatusCode(Response::HTTP_NOT_FOUND, $this->client->getResponse());

        $this->client->request(Request::METHOD_GET, sprintf('/action-citoyenne/%s', $citizenAction->getSlug()));

        $this->assertSame('S\'inscrire', $this->client->getCrawler()->filter('a.newbtn--orange')->text());
        $this->assertNull($this->getEventRegistrationRepository()->findAdherentRegistration($uuid, LoadAdherentData::ADHERENT_2_UUID));
    }

    protected function setUp()
    {
        parent::setUp();

        $this->init([
            LoadCitizenActionData::class,
        ]);
    }

    protected function tearDown()
    {
        $this->kill();

        parent::tearDown();
    }

    protected function getEventUrl(): string
    {
        return '/initiative-citoyenne/'.date('Y-m-d', strtotime('tomorrow')).'-apprenez-a-sauver-des-vies';
    }
}
