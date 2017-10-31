<?php

namespace AppBundle\Geocoder\Subscriber;

use AppBundle\CitizenInitiative\CitizenInitiativeUpdatedEvent;
use AppBundle\Committee\CommitteeWasUpdatedEvent;
use AppBundle\CitizenInitiative\CitizenInitiativeCreatedEvent;
use AppBundle\Event\EventUpdatedEvent;
use AppBundle\Events;
use AppBundle\Committee\CommitteeWasCreatedEvent;
use AppBundle\Event\EventCreatedEvent;
use AppBundle\Geocoder\Exception\GeocodingException;
use AppBundle\Geocoder\GeocoderInterface;
use AppBundle\Geocoder\GeoPointInterface;
use AppBundle\Group\GroupWasCreatedEvent;
use AppBundle\Membership\AdherentAccountWasCreatedEvent;
use AppBundle\Membership\AdherentEvents;
use AppBundle\Membership\AdherentProfileWasUpdatedEvent;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EntityAddressGeocodingSubscriber implements EventSubscriberInterface
{
    private $geocoder;
    private $manager;

    public function __construct(GeocoderInterface $geocoder, ObjectManager $manager)
    {
        $this->geocoder = $geocoder;
        $this->manager = $manager;
    }

    public function onAdherentAccountRegistrationCompleted(AdherentAccountWasCreatedEvent $event)
    {
        $this->updateGeocodableEntity($event->getAdherent());
    }

    public function onAdherentProfileUpdated(AdherentProfileWasUpdatedEvent $event)
    {
        $adherent = $event->getAdherent();

        if (!$adherent->getLatitude()) {
            $this->updateGeocodableEntity($adherent);
        }
    }

    public function onCommitteeCreated(CommitteeWasCreatedEvent $event)
    {
        $this->updateGeocodableEntity($event->getCommittee());
    }

    public function onCommitteeUpdated(CommitteeWasUpdatedEvent $event)
    {
        $this->updateGeocodableEntity($event->getCommittee());
    }

    public function onEventCreated(EventCreatedEvent $event)
    {
        $this->updateGeocodableEntity($event->getEvent());
    }

    public function onEventUpdated(EventUpdatedEvent $event)
    {
        $this->updateGeocodableEntity($event->getEvent());
    }

    private function updateGeocodableEntity(GeoPointInterface $geocodable)
    {
        if ($coordinates = $this->geocode($geocodable->getGeocodableAddress())) {
            $geocodable->updateCoordinates($coordinates);
            $this->manager->flush();
        }
    }

    public function onCitizenInitiativeCreated(CitizenInitiativeCreatedEvent $initiative)
    {
        $this->updateGeocodableEntity($initiative->getCitizenInitiative());
    }

    public function onCitizenInitiativeUpdated(CitizenInitiativeUpdatedEvent $initiative)
    {
        $this->updateGeocodableEntity($initiative->getCitizenInitiative());
    }

    public function onGroupCreated(GroupWasCreatedEvent $event)
    {
        $this->updateGeocodableEntity($event->getGroup());
    }

    private function geocode(string $address)
    {
        try {
            return $this->geocoder->geocode($address);
        } catch (GeocodingException $e) {
            // do nothing when an exception arises
            return null;
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            AdherentEvents::REGISTRATION_COMPLETED => ['onAdherentAccountRegistrationCompleted', -256],
            AdherentEvents::PROFILE_UPDATED => ['onAdherentProfileUpdated', -256],
            Events::COMMITTEE_CREATED => ['onCommitteeCreated', -256],
            Events::COMMITTEE_UPDATED => ['onCommitteeUpdated', -256],
            Events::EVENT_CREATED => ['onEventCreated', -256],
            Events::EVENT_UPDATED => ['onEventUpdated', -256],
            Events::CITIZEN_INITIATIVE_CREATED => ['onCitizenInitiativeCreated', -256],
            Events::CITIZEN_INITIATIVE_UPDATED => ['onCitizenInitiativeUpdated', -256],
            Events::GROUP_CREATED => ['onGroupCreated', -256],
        ];
    }
}
