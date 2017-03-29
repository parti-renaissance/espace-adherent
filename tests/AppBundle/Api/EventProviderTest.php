<?php

namespace Tests\AppBundle\Api;

use AppBundle\Api\EventProvider;
use AppBundle\Entity\Event;
use AppBundle\Repository\EventRepository;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class EventProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testGetUpcomingEvents()
    {
        $events[] = $event1 = $this->createCommitteeEventMock(
            '2bb0472c-4189-409f-9d06-b617843230ea',
            'evenement-paris',
            'Événement de Paris',
            48.8705073,
            2.3032432
        );

        $events[] = $event2 = $this->createCommitteeEventMock(
            'ae65d178-3dc6-4c14-843c-36df38c82834',
            'evenement-berlin',
            'Événement de Berlin',
            52.5330939,
            13.4662418
        );

        // This one is not geocoded and will not included in the final results
        $events[] = $event3 = $this->createCommitteeEventMock(
            'b3d93750-d983-46bb-8f36-1a7ce39e74b5',
            'evenement-rouen',
            'Événement de Rouen'
        );

        $repository = $this->createMock(EventRepository::class);
        $repository->expects($this->once())->method('findUpcomingEvents')->willReturn($events);

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->expects($this->at(0))->method('generate')->willReturn('/evenements/'.$event1->getUuid().'/'.$event1->getSlug());
        $urlGenerator->expects($this->at(1))->method('generate')->willReturn('/evenements/'.$event2->getUuid().'/'.$event2->getSlug());

        $provider = new EventProvider($repository, $urlGenerator);

        $this->assertCount(2, $data = $provider->getUpcomingEvents());
        $this->assertSame(
            [
                [
                    'uuid' => '2bb0472c-4189-409f-9d06-b617843230ea',
                    'slug' => 'evenement-paris',
                    'name' => 'Événement de Paris',
                    'url' => '/evenements/2bb0472c-4189-409f-9d06-b617843230ea/evenement-paris',
                    'position' => [
                        'lat' => 48.8705073,
                        'lng' => 2.3032432,
                    ],
                ],
                [
                    'uuid' => 'ae65d178-3dc6-4c14-843c-36df38c82834',
                    'slug' => 'evenement-berlin',
                    'name' => 'Événement de Berlin',
                    'url' => '/evenements/ae65d178-3dc6-4c14-843c-36df38c82834/evenement-berlin',
                    'position' => [
                        'lat' => 52.5330939,
                        'lng' => 13.4662418,
                    ],
                ],
            ],
            $data
        );
    }

    private function createCommitteeEventMock(string $uuid, string $slug, string $name, float $latitude = null, float $longitude = null)
    {
        $event = $this->createMock(Event::class);
        $event->expects($this->any())->method('isGeocoded')->willReturn($latitude && $longitude);
        $event->expects($this->any())->method('getUuid')->willReturn(Uuid::fromString($uuid));
        $event->expects($this->any())->method('getSlug')->willReturn($slug);
        $event->expects($this->any())->method('getName')->willReturn($name);
        $event->expects($this->any())->method('getLatitude')->willReturn($latitude);
        $event->expects($this->any())->method('getLongitude')->willReturn($longitude);

        return $event;
    }
}
