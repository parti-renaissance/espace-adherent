<?php

namespace App\Event;

use App\Address\Address;
use App\Address\AddressInterface;
use App\Address\PostAddressFactory;
use App\Entity\Event\Event;
use App\Geo\ZoneMatcher;
use App\Image\ImageManager;
use Ramsey\Uuid\Uuid;

class EventFactory
{
    private $addressFactory;
    private $zoneMatcher;
    private $imageManager;

    public function __construct(
        ImageManager $imageManager,
        ZoneMatcher $zoneMatcher,
        ?PostAddressFactory $addressFactory = null,
    ) {
        $this->addressFactory = $addressFactory ?: new PostAddressFactory();
        $this->zoneMatcher = $zoneMatcher;
        $this->imageManager = $imageManager;
    }

    public function createFromArray(array $data): Event
    {
        foreach (['uuid', 'name', 'category', 'description', 'address', 'begin_at', 'finish_at', 'capacity'] as $key) {
            if (empty($data[$key])) {
                throw new \InvalidArgumentException(\sprintf('Key "%s" is missing or has an empty value.', $key));
            }
        }

        $event = new Event(Uuid::fromString($data['uuid']));
        $event->setAuthor($data['organizer'] ?? null);
        $event->setCommittee($data['committee'] ?? null);
        $event->setName($data['name']);
        $event->setCategory($data['category']);
        $event->setDescription($data['description']);
        $event->setPostAddress($data['address']);
        $event->setBeginAt(new \DateTimeImmutable($data['begin_at']));
        $event->setFinishAt(new \DateTimeImmutable($data['finish_at']));
        $event->setCapacity($data['capacity']);

        if (!empty($data['time_zone'])) {
            $event->setTimeZone($data['time_zone']);
        }

        if (!empty($data['visio_url'])) {
            $event->setVisioUrl($data['visio_url']);
        }

        $event->setPrivate($data['private'] ?? false);
        $event->setElectoral($data['electoral'] ?? false);

        foreach ($this->zoneMatcher->match($event->getPostAddress()) as $zone) {
            $event->addZone($zone);
        }

        return $event;
    }

    public function createFromEventCommand(EventCommand $command): Event
    {
        $event = new Event($command->getUuid());
        $event->setCommittee($command->getCommittee());
        $event->setMode($command->getMode());
        $event->setAuthor($command->getAuthor());
        $event->setName($command->getName());
        $event->setCategory($command->getCategory());
        $event->setDescription($command->getDescription());
        $event->setPostAddress($this->createPostAddress($command->getAddress()));
        $event->setBeginAt($command->getBeginAt());
        $event->setFinishAt($command->getFinishAt());
        $event->setCapacity($command->getCapacity());
        $event->setTimeZone($command->getTimeZone());
        $event->setVisioUrl($command->getVisioUrl());
        $event->setImage($command->getImage());
        $event->setPrivate($command->isPrivate());
        $event->setElectoral($command->isElectoral());

        if ($event->getImage()) {
            $this->imageManager->saveImage($event);
        }

        return $event;
    }

    public function updateFromEventCommand(Event $event, EventCommand $command): void
    {
        $event->update(
            $command->getName(),
            $command->getDescription(),
            $this->createPostAddress($command->getAddress()),
            $command->getTimeZone(),
            $command->getBeginAt(),
            $command->getFinishAt(),
            $command->getVisioUrl(),
            $command->getCapacity(),
            $command->isPrivate(),
            $command->isElectoral()
        );

        $event->setMode($command->getMode());
        $event->setCategory($command->getCategory());
        $event->setImage($command->getImage());
        $event->setRemoveImage($command->isRemoveImage());

        if ($event->isRemoveImage() && $event->hasImageName()) {
            $this->imageManager->removeImage($event);
        } elseif ($event->getImage()) {
            $this->imageManager->saveImage($event);
        }
    }

    private function createPostAddress(Address $address): AddressInterface
    {
        return $this->addressFactory->createFromAddress($address, true);
    }
}
