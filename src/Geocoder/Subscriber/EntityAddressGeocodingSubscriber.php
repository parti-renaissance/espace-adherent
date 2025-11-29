<?php

declare(strict_types=1);

namespace App\Geocoder\Subscriber;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Committee\Event\CommitteeEventInterface;
use App\Donation\DonationEvents;
use App\Entity\Event\Event;
use App\Events;
use App\Geocoder\Coordinates;
use App\Geocoder\Event\DefaultEvent;
use App\Geocoder\Exception\GeocodingException;
use App\Geocoder\GeocodableEntityEventInterface;
use App\Geocoder\GeocodableInterface;
use App\Geocoder\Geocoder;
use App\Geocoder\GeoPointInterface;
use App\Membership\UserEvents;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class EntityAddressGeocodingSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly Geocoder $geocoder,
        private readonly EntityManagerInterface $manager,
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
        if (!\in_array($viewEvent->getRequest()->getMethod(), [Request::METHOD_POST, Request::METHOD_PUT])) {
            return;
        }

        $object = $viewEvent->getControllerResult();
        if (!$object instanceof GeocodableInterface || $object instanceof Event) {
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
            UserEvents::USER_CREATED => ['updateCoordinates', -256],
            UserEvents::USER_UPDATED => ['updateCoordinates', -256],

            CommitteeEventInterface::class => ['updateCoordinates', -256],

            Events::EVENT_CREATED => ['updateCoordinates', -256],
            Events::EVENT_UPDATED => ['updateCoordinates', -256],

            DonationEvents::CREATED => ['updateCoordinates', -256],
            DonationEvents::UPDATED => ['updateCoordinates', -256],

            KernelEvents::VIEW => ['apiUpdateCoordinates', EventPriorities::POST_WRITE],
        ];
    }
}
