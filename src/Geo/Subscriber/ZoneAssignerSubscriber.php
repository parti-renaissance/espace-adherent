<?php

namespace App\Geo\Subscriber;

use App\Committee\CommitteeEvent;
use App\Event\EventEvent;
use App\Events;
use App\Geo\ZoneMatcher;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ZoneAssignerSubscriber implements EventSubscriberInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var ZoneMatcher
     */
    private $zoneMatcher;

    public function __construct(EntityManagerInterface $em, ZoneMatcher $zoneMatcher)
    {
        $this->em = $em;
        $this->zoneMatcher = $zoneMatcher;
    }

    public function assignZoneToCommittee(CommitteeEvent $event): void
    {
        $committee = $event->getCommittee();
        if (!$committee || !$event->isAddressChanged()) {
            return;
        }

        $zones = $this->zoneMatcher->match($committee->getPostAddress());
        if (!$zones) {
            return;
        }

        $committee->clearZones();
        foreach ($zones as $zone) {
            $committee->addZone($zone);
        }

        $this->em->flush();
    }

    public function assignZoneToEvent(EventEvent $eventEvent): void
    {
        $event = $eventEvent->getEvent();
        if (!$event || !$eventEvent->isAddressChanged()) {
            return;
        }

        $zones = $this->zoneMatcher->match($event->getPostAddressModel());
        if (!$zones) {
            return;
        }

        $event->clearZones();
        foreach ($zones as $zone) {
            $event->addZone($zone);
        }

        $this->em->flush();
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::COMMITTEE_CREATED => ['assignZoneToCommittee', -1024],
            Events::COMMITTEE_UPDATED => ['assignZoneToCommittee', -1024],
            Events::EVENT_CREATED => ['assignZoneToEvent', -1024],
            Events::EVENT_UPDATED => ['assignZoneToEvent', -1024],
        ];
    }
}
