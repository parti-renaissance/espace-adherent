<?php

namespace App\Geocoder\Subscriber;

use App\Donation\DonationEvents;
use App\Events;
use App\Geocoder\Coordinates;
use App\Geocoder\Exception\GeocodingException;
use App\Geocoder\GeocodableEntityEventInterface;
use App\Geocoder\Geocoder;
use App\Geocoder\GeoPointInterface;
use App\Membership\AdherentEvents;
use App\Membership\Event\AdherentProfileWasUpdatedEvent;
use Doctrine\ORM\EntityManagerInterface as ObjectManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EntityAddressGeocodingSubscriber implements EventSubscriberInterface
{
    private $geocoder;
    private $manager;

    public function __construct(Geocoder $geocoder, ObjectManager $manager)
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

    /**
     * @return bool True if hash has changed, false otherwise
     */
    private function updateGeocodableEntity(GeoPointInterface $geocodable): bool
    {
        if ($geocodable->getGeocodableHash() !== $hash = md5($address = $geocodable->getGeocodableAddress())) {
            if ($coordinates = $this->geocode($address)) {
                $geocodable->updateCoordinates($coordinates);
                $geocodable->setGeocodableHash($hash);

                $this->manager->flush();

                return true;
            }
        }

        return false;
    }

    public function updateCoordinates(GeocodableEntityEventInterface $event): void
    {
        if ($this->updateGeocodableEntity($event->getGeocodableEntity())) {
            $event->markAddressAsChanged();
        }
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
            DonationEvents::CREATED => ['updateCoordinates', -256],
            DonationEvents::UPDATED => ['updateCoordinates', -256],
        ];
    }
}
