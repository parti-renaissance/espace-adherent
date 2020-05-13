<?php

namespace App\Geocoder\Subscriber;

use App\Donation\DonationEvents;
use App\Events;
use App\Geocoder\Coordinates;
use App\Geocoder\Exception\GeocodingException;
use App\Geocoder\GeocodableEntityEventInterface;
use App\Geocoder\GeocoderInterface;
use App\Geocoder\GeoPointInterface;
use App\Membership\AdherentEvents;
use App\Membership\AdherentProfileWasUpdatedEvent;
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

    public function onAdherentProfileUpdated(AdherentProfileWasUpdatedEvent $event): void
    {
        $adherent = $event->getAdherent();

        if (!$adherent->getLatitude()) {
            $this->updateGeocodableEntity($adherent);
        }
    }

    private function updateGeocodableEntity(GeoPointInterface $geocodable): void
    {
        if ($geocodable->getGeocodableHash() !== $hash = md5($address = $geocodable->getGeocodableAddress())) {
            if ($coordinates = $this->geocode($address)) {
                $geocodable->updateCoordinates($coordinates);
                $geocodable->setGeocodableHash($hash);

                $this->manager->flush();
            }
        }
    }

    public function updateCoordinates(GeocodableEntityEventInterface $event): void
    {
        $this->updateGeocodableEntity($event->getGeocodableEntity());
    }

    private function geocode(string $address): ?Coordinates
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
            AdherentEvents::REGISTRATION_COMPLETED => ['updateCoordinates', -256],
            AdherentEvents::PROFILE_UPDATED => ['onAdherentProfileUpdated', -256],
            Events::COMMITTEE_CREATED => ['updateCoordinates', -256],
            Events::COMMITTEE_UPDATED => ['updateCoordinates', -256],
            Events::EVENT_CREATED => ['updateCoordinates', -256],
            Events::EVENT_UPDATED => ['updateCoordinates', -256],
            Events::CITIZEN_ACTION_CREATED => ['updateCoordinates', -256],
            Events::CITIZEN_ACTION_UPDATED => ['updateCoordinates', -256],
            Events::CITIZEN_PROJECT_CREATED => ['updateCoordinates', -256],
            Events::CITIZEN_PROJECT_UPDATED => ['updateCoordinates', -256],
            DonationEvents::CREATED => ['updateCoordinates', -256],
            DonationEvents::UPDATED => ['updateCoordinates', -256],
        ];
    }
}
