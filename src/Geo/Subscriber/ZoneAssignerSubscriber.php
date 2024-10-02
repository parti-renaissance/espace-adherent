<?php

namespace App\Geo\Subscriber;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Address\AddressInterface;
use App\Committee\Event\CommitteeEvent;
use App\Entity\Action\Action;
use App\Entity\Event\CommitteeEvent as BaseCommitteeEvent;
use App\Entity\Geo\Zone;
use App\Entity\ZoneableEntityInterface;
use App\Event\EventEvent;
use App\Events;
use App\Geo\ZoneMatcher;
use App\Scope\ScopeGeneratorResolver;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ZoneAssignerSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ZoneMatcher $zoneMatcher,
        private readonly ScopeGeneratorResolver $scopeGeneratorResolver,
    ) {
    }

    public function assignZoneToCommittee(CommitteeEvent $event): void
    {
        $committee = $event->getCommittee();
        if (!$committee || !$event->isAddressChanged()) {
            return;
        }

        $this->assignZone($committee, $committee->getPostAddress());
    }

    public function assignZoneToAction(ViewEvent $event): void
    {
        $action = $event->getControllerResult();
        if (!$action instanceof Action) {
            return;
        }

        $this->assignZone($action, $action->getPostAddress());
    }

    public function assignZoneToEvent(EventEvent $eventEvent): void
    {
        $event = $eventEvent->getEvent();

        if ($event->getZones()->isEmpty() && $event instanceof BaseCommitteeEvent) {
            /** @var Zone $firstZone */
            $firstZone = $event->getCommittee()->getZones()->first();

            if ($firstZone->isCountry() || Zone::CUSTOM === $firstZone->getType()) {
                $event->setZones($event->getCommittee()->getZones()->getParentsOfType(Zone::COUNTRY));
            } else {
                $event->setZones($event->getCommittee()->getZones()->getParentsOfType(Zone::DEPARTMENT));
            }
        }

        if ($event->getZones()->isEmpty() && $scope = $this->scopeGeneratorResolver->generate()) {
            $event->setZones($scope->getZones());
        }

        $this->em->flush();
    }

    public function assignZone(ZoneableEntityInterface $entity, AddressInterface $address, bool $setCity = false): void
    {
        $zones = $this->zoneMatcher->match($address);
        if (!$zones) {
            return;
        }

        $entity->clearZones();
        $needInsee = method_exists($entity, 'setCity') && AddressInterface::FRANCE === $address->getCountry()
            && null == $address->getInseeCode() && null != $address->getPostalCode();
        foreach ($zones as $zone) {
            $entity->addZone($zone);
            if ($setCity && $needInsee && $zone->isCity()) {
                $entity->setCity(\sprintf('%s-%s', $address->getPostalCode(), $zone->getCode()));
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
            KernelEvents::VIEW => ['assignZoneToAction', EventPriorities::PRE_WRITE],
        ];
    }
}
