<?php

namespace Tests\AppBundle\Controller\EnMarche;

use AppBundle\DataFixtures\ORM\LoadCitizenActionData;
use AppBundle\Entity\CitizenAction;
use Symfony\Component\HttpFoundation\Request;
use Tests\AppBundle\Controller\ControllerTestTrait;
use Tests\AppBundle\MysqlWebTestCase;

/**
 * @group functional
 * @group time-sensitive
 */
class CitizenActionControllerTest extends MysqlWebTestCase
{
    use ControllerTestTrait;

    public function testExportIcalAction(): void
    {
        $uuid = LoadCitizenActionData::CITIZEN_ACTION_3_UUID;
        /** @var CitizenAction $citizenAction */
        $citizenAction = $this->getRepository(CitizenAction::class)->findOneBy(['uuid' => $uuid]);

        $this->client->request(Request::METHOD_GET, '/action-citoyenne/2017-12-30-projet-citoyen-3/ical');

        $this->isSuccessful($response = $this->client->getResponse());
        $this->assertSame('attachment; filename='.$citizenAction->getFinishAt()->format('Y-m-d').'-projet-citoyen-3.ics', $response->headers->get('Content-Disposition'));
        $this->assertSame('text/calendar; charset=UTF-8', $response->headers->get('Content-Type'));

        $currentDate = date('Ymd\THis\Z');
        $beginAt = $citizenAction->getBeginAt()->format('Ymd\THis');
        $finishAt = $citizenAction->getFinishAt()->format('Ymd\THis');
        $ical = <<<CONTENT
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//Sabre//Sabre VObject 4.1.4//EN
CALSCALE:GREGORIAN
ORGANIZER:CN="Jacques PICARD"\;mailto:jacques.picard@en-marche.fr
BEGIN:VEVENT
UID:$uuid
DTSTAMP:$currentDate
SUMMARY:Projet citoyen #3
DESCRIPTION:Un troisième projet citoyen
DTSTART:$beginAt
DTEND:$finishAt
LOCATION:16 rue de la Paix\, 75008 Paris 8e
END:VEVENT
END:VCALENDAR

CONTENT;
        $ical = str_replace("\n", "\r\n", $ical); // Returned content contains CRLF
        $this->assertSame($ical, $response->getContent());
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
