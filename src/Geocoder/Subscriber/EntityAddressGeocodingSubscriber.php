<?php

namespace App\Geocoder\Subscriber;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Donation\DonationEvents;
use App\Entity\Event\BaseEvent;
use App\Events;
use App\Geocoder\Coordinates;
use App\Geocoder\Event\DefaultEvent;
use App\Geocoder\Exception\GeocodingException;
use App\Geocoder\GeocodableEntityEventInterface;
use App\Geocoder\GeocodableInterface;
use App\Geocoder\Geocoder;
use App\Geocoder\GeoPointInterface;
use App\Membership\AdherentEvents;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class EntityAddressGeocodingSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly Geocoder $geocoder,
        private readonly EntityManagerInterface $manager
    ) {
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
            } else {
                $geocodable->resetCoordinates();
            }

            $this->manager->flush();

            return true;
        }

        return false;
    }

    public function updateCoordinates(GeocodableEntityEventInterface $object): void
    {
        if ($this->updateGeocodableEntity($object->getGeocodableEntity())) {
            $object->markAddressAsChanged();
        }
    }

    public function apiUpdateCoordinates(ViewEvent $viewEvent): void
    {
        $object = $viewEvent->getControllerResult();
        if (!$object instanceof GeocodableInterface || $object instanceof BaseEvent) {
            return;
        }

        $this->updateCoordinates(new DefaultEvent($object));
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

    public static function getSubscribedEvents(): array
    {
        return [
            AdherentEvents::REGISTRATION_COMPLETED => ['updateCoordinates', -256],
            AdherentEvents::PROFILE_UPDATED => ['updateCoordinates', -256],
            Events::COMMITTEE_CREATED => ['updateCoordinates', -256],
            Events::COMMITTEE_UPDATED => ['updateCoordinates', -256],
            Events::EVENT_CREATED => ['updateCoordinates', -256],
            Events::EVENT_UPDATED => ['updateCoordinates', -256],
            DonationEvents::CREATED => ['updateCoordinates', -256],
            DonationEvents::UPDATED => ['updateCoordinates', -256],
            KernelEvents::VIEW => ['apiUpdateCoordinates', EventPriorities::POST_WRITE],
        ];
    }
}
