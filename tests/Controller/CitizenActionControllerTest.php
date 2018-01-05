<?php

namespace Tests\AppBundle\Controller\EnMarche;

use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\DataFixtures\ORM\LoadCitizenActionData;
use AppBundle\Entity\CitizenAction;
use AppBundle\Entity\EventRegistration;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\Controller\ControllerTestTrait;
use Tests\AppBundle\MysqlWebTestCase;

/**
 * @group functional
 */
class CitizenActionControllerTest extends MysqlWebTestCase
{
    use ControllerTestTrait;

    public function testExportIcalAction(): void
    {
        $uuid = LoadCitizenActionData::CITIZEN_ACTION_3_UUID;
        /** @var CitizenAction $citizenAction */
        $citizenAction = $this->getRepository(CitizenAction::class)->findOneBy(['uuid' => $uuid]);

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
PRODID\:\-\/\/Sabre\/\/Sabre VObject 4\.1\.4\/\/EN
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
        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr', 'changeme1337');

        $uuid = LoadCitizenActionData::CITIZEN_ACTION_3_UUID;
        /** @var CitizenAction $citizenAction */
        $citizenAction = $this->getRepository(CitizenAction::class)->findOneBy(['uuid' => $uuid]);

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

        $this->authenticateAsAdherent($this->client, 'carl999@example.fr', 'secret!12345');

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
}
