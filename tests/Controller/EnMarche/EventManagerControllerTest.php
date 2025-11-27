<?php

namespace Tests\App\Controller\EnMarche;

use App\DataFixtures\ORM\LoadCommitteeEventData;
use App\Entity\Event\Event;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractEnMarcheWebTestCase;
use Tests\App\Controller\ControllerTestTrait;

#[Group('functional')]
#[Group('eventManager')]
class EventManagerControllerTest extends AbstractEnMarcheWebTestCase
{
    use ControllerTestTrait;

    #[DataProvider('provideCancelledInaccessiblePages')]
    public function testRegisteredAdherentUserCannotFoundPagesOfCancelledEvent($path)
    {
        $this->authenticateAsAdherent($this->client, 'benjyd@aol.com');

        $this->redirectionEventNotPublishTest($path);
    }

    public static function provideCancelledInaccessiblePages(): array
    {
        $slug = self::getRelativeDate('2018-05-18', '+3 days').'-reunion-de-reflexion-parisienne-annule';

        return [
            ['/evenements/'.$slug.'/modifier'],
            ['/evenements/'.$slug.'/inscription'],
            ['/evenements/'.$slug.'/annuler'],
        ];
    }

    public function testExportIcalEvent()
    {
        $this->client->request('GET', '/evenements/'.self::getRelativeDate('2018-05-18', '+3 days').'-reunion-de-reflexion-parisienne/ical');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
    }

    public function testExportIcalForeignEvent()
    {
        $uuid = LoadCommitteeEventData::EVENT_12_UUID;
        /** @var Event $event */
        $event = $this->getEventRepository()->findOneBy(['uuid' => $uuid]);

        $this->client->request(Request::METHOD_GET, \sprintf('/evenements/%s/ical', $event->getSlug()));
        $this->isSuccessful($response = $this->client->getResponse());
        self::assertSame(\sprintf('attachment; filename=%s.ics', $event->getSlug()), $response->headers->get('Content-Disposition'));
        self::assertSame('text/calendar; charset=UTF-8', $response->headers->get('Content-Type'));

        $beginAt = preg_quote($event->getLocalBeginAt()->format('Ymd\THis'), '/');
        $finishAt = preg_quote($event->getLocalFinishAt()->format('Ymd\THis'), '/');
        $uuid = preg_quote($uuid, '/');
        $icalRegex = <<<CONTENT
            BEGIN\:VCALENDAR
            VERSION\:2\.0
            PRODID\:\-\/\/Sabre\/\/Sabre VObject 4\.[0-9]+\.[0-9]+\/\/EN
            CALSCALE\:GREGORIAN
            ORGANIZER\:Pierre KIROULE
            BEGIN\:VEVENT
            UID\:$uuid
            DTSTAMP\:\\d{8}T\\d{6}Z
            SUMMARY\:Meeting de New York City
            DESCRIPTION\:Ouvert aux français de New York\.
            DTSTART;TZID=America\/New_York\:$beginAt
            DTEND;TZID=America\/New_York\:$finishAt
            LOCATION\:226 W 52nd St\\\\, 10019 New York\\\\, \États\-Unis
            END\:VEVENT
            END\:VCALENDAR
            CONTENT;
        $icalRegex = str_replace("\n", "\r\n", $icalRegex); // Returned content contains CRLF

        $this->assertMatchesRegularExpression(\sprintf('/%s/', $icalRegex), $response->getContent());
    }

    private function redirectionEventNotPublishTest($url)
    {
        $this->client->request(Request::METHOD_GET, $url);

        $this->assertClientIsRedirectedTo('/evenements', $this->client, false, true);

        $this->client->followRedirect();

        $this->isSuccessful($this->client->getResponse());
    }

    private static function getRelativeDate(string $date, string $modifier, string $format = 'Y-m-d'): string
    {
        return (new \DateTime($date))->modify($modifier)->format($format);
    }
}
