<?php

namespace App\Event;

use App\Address\Address;
use App\Address\PostAddressFactory;
use App\CitizenAction\CitizenActionCommand;
use App\Entity\Event\BaseEvent;
use App\Entity\Event\CitizenAction;
use App\Entity\Event\CommitteeEvent;
use App\Entity\Event\DefaultEvent;
use App\Entity\Event\InstitutionalEvent;
use App\Entity\PostAddress;
use App\Geo\ZoneMatcher;
use App\InstitutionalEvent\InstitutionalEventCommand;
use App\Referent\ReferentTagManager;
use Ramsey\Uuid\Uuid;

class EventFactory
{
    private $addressFactory;

    /**
     * @var ZoneMatcher
     */
    private $zoneMatcher;

    private $referentTagManager;

    public function __construct(
        ReferentTagManager $referentTagManager,
        ZoneMatcher $zoneMatcher,
        PostAddressFactory $addressFactory = null
    ) {
        $this->addressFactory = $addressFactory ?: new PostAddressFactory();
        $this->zoneMatcher = $zoneMatcher;
        $this->referentTagManager = $referentTagManager;
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

    public function createCitizenActionFromArray(array $data): CitizenAction
    {
        foreach (['uuid', 'organizer', 'citizen_project', 'name', 'category', 'description', 'address', 'begin_at', 'finish_at'] as $key) {
            if (!\array_key_exists($key, $data)) {
                throw new \InvalidArgumentException(sprintf('Key "%s" is missing.', $key));
            }
        }

        $uuid = Uuid::fromString($data['uuid']);
        $citizenAction = new CitizenAction(
            $uuid,
            $data['organizer'],
            $data['citizen_project'],
            $data['name'],
            $data['category'],
            $data['description'],
            $data['address'],
            $data['begin_at'],
            $data['finish_at']
        );
        if (!empty($data['time_zone'])) {
            $citizenAction->setTimeZone($data['time_zone']);
        }

        if (!empty($data['visio_url'])) {
            $citizenAction->setVisioUrl($data['visio_url']);
        }

        $this->referentTagManager->assignReferentLocalTags($citizenAction);

        return $citizenAction;
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
            default:
                $event = new DefaultEvent($command->getUuid());
                $event->setAuthor($command->getAuthor());
                $event->setName($command->getName());
                $event->setCategory($command->getCategory());
                $event->setDescription($command->getDescription());
                $event->setPostAddress($this->createPostAddress($command->getAddress()));
                $event->setBeginAt($command->getBeginAt());
                $event->setFinishAt($command->getFinishAt());
                $event->setCapacity($command->getCapacity());
        }

        $event->setTimeZone($command->getTimeZone());
        $event->setVisioUrl($command->getVisioUrl());

        $this->referentTagManager->assignReferentLocalTags($event);

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

        $this->referentTagManager->assignReferentLocalTags($event);

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
        );

        if ($event instanceof CommitteeEvent) {
            $event->setIsForLegislatives($command->isForLegislatives());
        }

        $event->setCategory($command->getCategory());
        $this->referentTagManager->assignReferentLocalTags($event);
    }

    public function createFromCitizenActionCommand(CitizenActionCommand $command): CitizenAction
    {
        $citizenAction = new CitizenAction(
            $command->getUuid(),
            $command->getAuthor(),
            $command->getCitizenProject(),
            $command->getName(),
            $command->getCategory(),
            $command->getDescription(),
            $this->createPostAddress($command->getAddress()),
            $command->getBeginAt(),
            $command->getFinishAt(),
            0,
            [],
            $command->getTimeZone(),
            $command->getVisioUrl()
        );

        $this->referentTagManager->assignReferentLocalTags($citizenAction);

        return $citizenAction;
    }

    public function updateFromCitizenActionCommand(CitizenActionCommand $command, CitizenAction $citizenAction): void
    {
        $citizenAction->update(
            $command->getName(),
            $command->getDescription(),
            $this->createPostAddress($command->getAddress()),
            $command->getTimeZone(),
            $command->getBeginAt(),
            $command->getFinishAt(),
            $command->getVisioUrl()
        );

        $citizenAction->setCategory($command->getCategory());
        $this->referentTagManager->assignReferentLocalTags($citizenAction);
    }

    private function createPostAddress(Address $address): PostAddress
    {
        return $this->addressFactory->createFromAddress($address);
    }
}
