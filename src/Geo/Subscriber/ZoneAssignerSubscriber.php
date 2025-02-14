<?php

namespace App\Geo\Subscriber;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Address\AddressInterface;
use App\Committee\Event\CommitteeEventInterface;
use App\Entity\Action\Action;
use App\Entity\Adherent;
use App\Entity\Geo\Zone;
use App\Entity\ZoneableEntityInterface;
use App\Event\EventEvent;
use App\Events;
use App\Geo\ZoneMatcher;
use App\Membership\Event\UserEvent;
use App\Membership\UserEvents;
use App\Repository\Geo\ZoneRepository;
use App\Scope\ScopeGeneratorResolver;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ZoneAssignerSubscriber implements EventSubscriberInterface
{
    private const TYPES = [
        Zone::CANTON,
        Zone::DISTRICT,
        Zone::FOREIGN_DISTRICT,
        Zone::VOTE_PLACE,
    ];

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ZoneMatcher $zoneMatcher,
        private readonly ScopeGeneratorResolver $scopeGeneratorResolver,
        private readonly ZoneRepository $zoneRepository,
    ) {
    }

    public function assignZoneToCommittee(CommitteeEventInterface $event): void
    {
        if (!$event->isAddressChanged()) {
            return;
        }

        $committee = $event->getCommittee();

        $this->assignZone($committee, $committee->getPostAddress());

        $this->em->flush();
    }

    public function assignZoneToAction(ViewEvent $event): void
    {
        $action = $event->getControllerResult();
        if (!$action instanceof Action) {
            return;
        }

        $this->assignZone($action, $action->getPostAddress());

        $this->em->flush();
    }

    public function assignZoneToEvent(EventEvent $eventEvent): void
    {
        $event = $eventEvent->getEvent();

        if (!$event->getZones()->isEmpty()) {
            return;
        }

        if ($event->getCommittee()) {
            $event->setZones([$event->getCommittee()->getAssemblyZone()]);
        } elseif ($scope = $this->scopeGeneratorResolver->generate()) {
            $event->setZones($scope->getZones());
        }

        $this->em->flush();
    }

    public function assignZoneToAdherent(UserEvent $event): void
    {
        $adherent = $event->getAdherent();

        $toAdd = [];
        $typeForGeoSearch = self::TYPES;

        if ($adherent->isForeignResident()) {
            $toAdd = $this->zoneRepository->findParent(Zone::FOREIGN_DISTRICT, $adherent->getCountry(), Zone::COUNTRY);
            $typeForGeoSearch = [Zone::CUSTOM];
        }

        if ($adherent->isGeocoded()) {
            $latitude = $adherent->getLatitude();
            $longitude = $adherent->getLongitude();

            $toAdd = array_merge($toAdd, $this->zoneRepository->findByCoordinatesAndTypes($latitude, $longitude, $typeForGeoSearch));
        }

        $toAdd = $this->cleanFrenchZonesToAdd($adherent, $toAdd);

        $this->assignZone($adherent, $adherent->getPostAddress());

        if ($toAdd) {
            $toRemove = $adherent->getZones()->filter(
                static function (Zone $zone): bool {
                    return \in_array($zone->getType(), self::TYPES, true);
                }
            );

            foreach ($toRemove as $zone) {
                $adherent->removeZone($zone);
            }
        }

        foreach (array_unique($toAdd) as $zone) {
            $adherent->addZone($zone);
        }

        $this->em->flush();
    }

    private function assignZone(ZoneableEntityInterface $entity, AddressInterface $address): void
    {
        if (!$zones = $this->zoneMatcher->match($address)) {
            return;
        }

        $needInsee = method_exists($entity, 'setCity')
            && AddressInterface::FRANCE === $address->getCountry()
            && null == $address->getInseeCode()
            && null != $address->getPostalCode();

        $entity->clearZones();

        foreach ($zones as $zone) {
            $entity->addZone($zone);

            if ($needInsee && $zone->isCity()) {
                $entity->setCity(\sprintf('%s-%s', $address->getPostalCode(), $zone->getCode()));
            }
        }
    }

    /** @param Zone[] $toAdd */
    private function cleanFrenchZonesToAdd(Adherent $adherent, array $toAdd): array
    {
        if ($adherent->isForeignResident() || !($postalCode = $adherent->getPostalCode()) || 5 !== mb_strlen($postalCode)) {
            return $toAdd;
        }

        $toClean = $toKeep = [];

        foreach ($toAdd as $zone) {
            if (\in_array($zone->getType(), [Zone::DISTRICT, Zone::CANTON])) {
                $toClean[] = $zone;
            } else {
                $toKeep[] = $zone;
            }
        }

        foreach ($toClean as $key => $zone) {
            /** @var Zone $dptZone */
            $dptZone = current($zone->getParentsOfType(Zone::DEPARTMENT));

            if ($dptZone && !str_starts_with($postalCode, $dptZone->getCode())) {
                unset($toClean[$key]);
            }
        }

        return array_merge($toKeep, $toClean);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CommitteeEventInterface::class => ['assignZoneToCommittee', -1024],

            Events::EVENT_CREATED => ['assignZoneToEvent', -1024],
            Events::EVENT_UPDATED => ['assignZoneToEvent', -1024],

            KernelEvents::VIEW => ['assignZoneToAction', EventPriorities::PRE_WRITE],

            UserEvents::USER_CREATED => ['assignZoneToAdherent', -257],
            UserEvents::USER_UPDATED => ['assignZoneToAdherent', -257],
        ];
    }
}
