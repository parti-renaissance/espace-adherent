<?php

namespace App\Geo\Subscriber;

use App\Address\AddressInterface;
use App\Committee\CommitteeEvent;
use App\Entity\PostAddress;
use App\Entity\ZoneableEntity;
use App\Event\EventEvent;
use App\Events;
use App\Geo\ZoneMatcher;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ZoneAssignerSubscriber implements EventSubscriberInterface
{
    private EntityManagerInterface $em;
    private ZoneMatcher $zoneMatcher;

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

        $this->assignZone($committee, $committee->getPostAddress());
    }

    public function assignZoneToEvent(EventEvent $eventEvent): void
    {
        $event = $eventEvent->getEvent();
        if (!$event || !$eventEvent->isAddressChanged()) {
            return;
        }

        $this->assignZone($event, $event->getPostAddressModel(), true);
    }

    public function assignZone(ZoneableEntity $entity, AddressInterface $address, bool $setCity = false): void
    {
        $zones = $this->zoneMatcher->match($address);
        if (!$zones) {
            return;
        }

        $entity->clearZones();
        $needInsee = method_exists($entity, 'setCity') && PostAddress::FRANCE === $address->getCountry()
            && null == $address->getInseeCode() && null != $address->getPostalCode();
        foreach ($zones as $zone) {
            $entity->addZone($zone);
            if ($setCity && $needInsee && $zone->isCity()) {
                $entity->setCity(sprintf('%s-%s', $address->getPostalCode(), $zone->getCode()));
            }
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
