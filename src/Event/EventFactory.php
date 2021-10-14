<?php

namespace App\Event;

use App\Address\Address;
use App\Address\PostAddressFactory;
use App\Entity\Event\BaseEvent;
use App\Entity\Event\CoalitionEvent;
use App\Entity\Event\CommitteeEvent;
use App\Entity\Event\DefaultEvent;
use App\Entity\Event\InstitutionalEvent;
use App\Entity\PostAddress;
use App\Geo\ZoneMatcher;
use App\Image\ImageManager;
use App\InstitutionalEvent\InstitutionalEventCommand;
use App\Referent\ReferentTagManager;
use Ramsey\Uuid\Uuid;

class EventFactory
{
    private $addressFactory;
    private $zoneMatcher;
    private $referentTagManager;
    private $imageManager;

    public function __construct(
        ReferentTagManager $referentTagManager,
        ImageManager $imageManager,
        ZoneMatcher $zoneMatcher,
        PostAddressFactory $addressFactory = null
    ) {
        $this->addressFactory = $addressFactory ?: new PostAddressFactory();
        $this->zoneMatcher = $zoneMatcher;
        $this->referentTagManager = $referentTagManager;
        $this->imageManager = $imageManager;
    }

    public function createFromArray(array $data): CommitteeEvent
    {
        foreach (['uuid', 'name', 'category', 'description', 'address', 'begin_at', 'finish_at', 'capacity'] as $key) {
            if (empty($data[$key])) {
                throw new \InvalidArgumentException(sprintf('Key "%s" is missing or has an empty value.', $key));
            }
        }

        $uuid = Uuid::fromString($data['uuid']);

        $event = new CommitteeEvent(
            $uuid,
            $data['organizer'] ?? null,
            $data['committee'] ?? null,
            $data['name'],
            $data['category'],
            $data['description'],
            $data['address'],
            $data['begin_at'],
            $data['finish_at'],
            $data['capacity'],
            $data['is_for_legislatives'] ?? false
        );

        if (!empty($data['time_zone'])) {
            $event->setTimeZone($data['time_zone']);
        }

        if (!empty($data['visio_url'])) {
            $event->setVisioUrl($data['visio_url']);
        }

        $event->setPrivate($data['private'] ?? false);
        $event->setElectoral($data['electoral'] ?? false);

        $this->referentTagManager->assignReferentLocalTags($event);

        foreach ($this->zoneMatcher->match($event->getPostAddressModel()) as $zone) {
            $event->addZone($zone);
        }

        return $event;
    }

    public function createInstitutionalEventFromArray(array $data): InstitutionalEvent
    {
        foreach (['uuid', 'name', 'category', 'description', 'address', 'begin_at', 'finish_at', 'capacity'] as $key) {
            if (empty($data[$key])) {
                throw new \InvalidArgumentException(sprintf('Key "%s" is missing or has an empty value.', $key));
            }
        }

        $uuid = Uuid::fromString($data['uuid']);

        $event = new InstitutionalEvent(
            $uuid,
            $data['organizer'] ?? null,
            $data['name'],
            $data['category'],
            $data['description'],
            $data['address'],
            $data['begin_at'],
            $data['finish_at']
        );

        if (!empty($data['time_zone'])) {
            $event->setTimeZone($data['time_zone']);
        }

        if (!empty($data['visio_url'])) {
            $event->setVisioUrl($data['visio_url']);
        }

        $this->referentTagManager->assignReferentLocalTags($event);

        return $event;
    }

    public function createFromEventCommand(EventCommand $command, string $eventClass): BaseEvent
    {
        if (!is_a($eventClass, BaseEvent::class, true)) {
            throw new \InvalidArgumentException(sprintf('Invalid Event type: "%s"', $eventClass));
        }

        switch ($eventClass) {
            case CommitteeEvent::class:
                /** @var CommitteeEvent $event */
                $event = new CommitteeEvent(
                    $command->getUuid(),
                    $command->getAuthor(),
                    $command->getCommittee(),
                    $command->getName(),
                    $command->getCategory(),
                    $command->getDescription(),
                    $this->createPostAddress($command->getAddress()),
                    $command->getBeginAt()->format(\DATE_ATOM),
                    $command->getFinishAt()->format(\DATE_ATOM),
                    $command->getCapacity(),
                    $command->isForLegislatives()
                );
                break;
            case CoalitionEvent::class:
                /** @var CoalitionEvent $event */
                $event = new CoalitionEvent($command->getUuid());
                $event->setCoalition($command->getCoalition());
                break;
            default:
                $event = new DefaultEvent($command->getUuid());
        }

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

        $this->referentTagManager->assignReferentLocalTags($event);

        if ($event->getImage()) {
            $this->imageManager->saveImage($event);
        }

        return $event;
    }

    public function createFromInstitutionalEventCommand(InstitutionalEventCommand $command): InstitutionalEvent
    {
        $event = new InstitutionalEvent(
            $command->getUuid(),
            $command->getAuthor(),
            $command->getName(),
            $command->getCategory(),
            $command->getDescription(),
            $this->createPostAddress($command->getAddress()),
            $command->getBeginAt()->format(\DATE_ATOM),
            $command->getFinishAt()->format(\DATE_ATOM),
            $command->getInvitations()
        );

        $event->setTimeZone($command->getTimeZone());
        $event->setVisioUrl($command->getVisioUrl());
        $event->setImage($command->getImage());

        $this->referentTagManager->assignReferentLocalTags($event);

        if ($event->getImage()) {
            $this->imageManager->saveImage($event);
        }

        return $event;
    }

    public function updateFromInstitutionalEventCommand(
        InstitutionalEventCommand $command,
        InstitutionalEvent $institutionalEvent
    ): void {
        $institutionalEvent->update(
            $command->getName(),
            $command->getDescription(),
            $this->createPostAddress($command->getAddress()),
            $command->getTimeZone(),
            $command->getBeginAt(),
            $command->getFinishAt(),
            $command->getVisioUrl()
        );

        $institutionalEvent->setCategory($command->getCategory());
        $institutionalEvent->setInvitations($command->getInvitations());
        $institutionalEvent->setImage($command->getImage());
        $institutionalEvent->setRemoveImage($command->isRemoveImage());

        if ($institutionalEvent->isRemoveImage() && $institutionalEvent->hasImageName()) {
            $this->imageManager->removeImage($institutionalEvent);
        } elseif ($institutionalEvent->getImage()) {
            $this->imageManager->saveImage($institutionalEvent);
        }
    }

    public function updateFromEventCommand(BaseEvent $event, EventCommand $command): void
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

        if ($event instanceof CommitteeEvent) {
            $event->setIsForLegislatives($command->isForLegislatives());
        }

        $event->setMode($command->getMode());
        $event->setCategory($command->getCategory());
        $event->setImage($command->getImage());
        $event->setRemoveImage($command->isRemoveImage());
        $this->referentTagManager->assignReferentLocalTags($event);

        if ($event->isRemoveImage() && $event->hasImageName()) {
            $this->imageManager->removeImage($event);
        } elseif ($event->getImage()) {
            $this->imageManager->saveImage($event);
        }
    }

    private function createPostAddress(Address $address): PostAddress
    {
        return $this->addressFactory->createFromAddress($address);
    }
}
