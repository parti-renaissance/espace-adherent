<?php

namespace App\Geo\Subscriber;

use App\Committee\CommitteeEvent;
use App\Entity\Geo\Zone;
use App\Events;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ZoneAssignerSubscriber implements EventSubscriberInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function assignZoneToCommittee(CommitteeEvent $event): void
    {
        $committee = $event->getCommittee();
        if (!$committee || !$event->isAddressChanged()) {
            return;
        }

        $latitude = $committee->getLatitude();
        $longitude = $committee->getLongitude();
        $zones = $this->em
            ->getRepository(Zone::class)
            ->findByCoordinatesAndTypes($latitude, $longitude, [Zone::DISTRICT])
        ;

        $committee->clearZones();
        foreach ($zones as $zone) {
            $committee->addZone($zone);
        }

        $this->em->flush();
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::COMMITTEE_CREATED => ['assignZoneToCommittee', -1024],
            Events::COMMITTEE_UPDATED => ['assignZoneToCommittee', -1024],
        ];
    }
}
