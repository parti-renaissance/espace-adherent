<?php

namespace AppBundle\Geocoder\Subscriber;

use AppBundle\Committee\CommitteeEvents;
use AppBundle\Committee\CommitteeWasCreatedEvent;
use AppBundle\Donation\DonationEvents;
use AppBundle\Donation\DonationWasCreatedEvent;
use AppBundle\Committee\Event\CommitteeEventCreatedEvent;
use AppBundle\Geocoder\Exception\GeocodingException;
use AppBundle\Geocoder\GeocoderInterface;
use AppBundle\Geocoder\GeoPointInterface;
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

    public function onDonationCreated(DonationWasCreatedEvent $event)
    {
        $this->updateGeocodableEntity($event->getDonation());
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

    public function onCommitteeEventCreated(CommitteeEventCreatedEvent $event)
    {
        $this->updateGeocodableEntity($event->getCommitteeEvent());
    }

    private function updateGeocodableEntity(GeoPointInterface $geocodable)
    {
        if ($coordinates = $this->geocode($geocodable->getGeocodableAddress())) {
            $geocodable->updateCoordinates($coordinates);
            $this->manager->flush();
        }
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
            DonationEvents::CREATED => ['onDonationCreated', -256],
            AdherentEvents::REGISTRATION_COMPLETED => ['onAdherentAccountRegistrationCompleted', -256],
            AdherentEvents::PROFILE_UPDATED => ['onAdherentProfileUpdated', -256],
            CommitteeEvents::CREATED => ['onCommitteeCreated', -256],
            CommitteeEvents::EVENT_CREATED => ['onCommitteeEventCreated', -256],
        ];
    }
}
