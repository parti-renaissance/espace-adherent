<?php

namespace App\Event;

use App\Entity\Event\Event;
use App\Geo\ZoneMatcher;
use Ramsey\Uuid\Uuid;

class EventFactory
{
    public function __construct(private readonly ZoneMatcher $zoneMatcher)
    {
    }

    public function createFromArray(array $data): Event
    {
        foreach (['uuid', 'name', 'category', 'description', 'address', 'begin_at', 'finish_at', 'capacity'] as $key) {
            if (empty($data[$key])) {
                throw new \InvalidArgumentException(\sprintf('Key "%s" is missing or has an empty value.', $key));
            }
        }

        $event = new Event(Uuid::fromString($data['uuid']));
        $event->setAuthor($data['organizer'] ?? null);
        $event->setCommittee($data['committee'] ?? null);
        $event->setName($data['name']);
        $event->setCategory($data['category']);
        $event->setDescription($data['description']);
        $event->setPostAddress($data['address']);
        $event->setBeginAt(new \DateTimeImmutable($data['begin_at']));
        $event->setFinishAt(new \DateTimeImmutable($data['finish_at']));
        $event->setCapacity($data['capacity']);

        if (!empty($data['time_zone'])) {
            $event->setTimeZone($data['time_zone']);
        }

        if (!empty($data['visio_url'])) {
            $event->setVisioUrl($data['visio_url']);
        }

        $event->setPrivate($data['private'] ?? false);
        $event->setElectoral($data['electoral'] ?? false);

        foreach ($this->zoneMatcher->match($event->getPostAddress()) as $zone) {
            $event->addZone($zone);
        }

        return $event;
    }
}
