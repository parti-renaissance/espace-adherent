<?php

namespace AppBundle\Event;

use AppBundle\Address\Address;
use AppBundle\Address\PostAddressFactory;
use AppBundle\CitizenAction\CitizenActionCommand;
use AppBundle\Entity\CitizenAction;
use AppBundle\Entity\Event;
use AppBundle\Entity\InstitutionalEvent;
use AppBundle\Entity\PostAddress;
use AppBundle\InstitutionalEvent\InstitutionalEventCommand;
use AppBundle\Referent\ReferentTagManager;
use Ramsey\Uuid\Uuid;

class EventFactory
{
    private $addressFactory;
    private $referentTagManager;

    public function __construct(ReferentTagManager $referentTagManager, PostAddressFactory $addressFactory = null)
    {
        $this->addressFactory = $addressFactory ?: new PostAddressFactory();
        $this->referentTagManager = $referentTagManager;
    }

    public function createFromArray(array $data): Event
    {
        foreach (['uuid', 'name', 'category', 'description', 'address', 'begin_at', 'finish_at', 'capacity'] as $key) {
            if (empty($data[$key])) {
                throw new \InvalidArgumentException(sprintf('Key "%s" is missing or has an empty value.', $key));
            }
        }

        $uuid = Uuid::fromString($data['uuid']);

        $event = new Event(
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

        $this->referentTagManager->assignReferentLocalTags($event);

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

        $this->referentTagManager->assignReferentLocalTags($event);

        return $event;
    }

    public function createCitizenActionFromArray(array $data): CitizenAction
    {
        foreach (['uuid', 'organizer', 'citizen_project', 'name', 'category', 'description', 'address', 'begin_at', 'finish_at'] as $key) {
            if (!array_key_exists($key, $data)) {
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

        $this->referentTagManager->assignReferentLocalTags($citizenAction);

        return $citizenAction;
    }

    public function createFromEventCommand(EventCommand $command): Event
    {
        $event = new Event(
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
        $event->setTimeZone($command->getTimeZone());

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

        $this->referentTagManager->assignReferentLocalTags($event);

        return $event;
    }

    public function updateFromInstitutionalEventCommand(
        InstitutionalEventCommand $command,
        InstitutionalEvent $institutionalEvent
    ): void {
        $institutionalEvent->update(
            $command->getName(),
            $command->getCategory(),
            $command->getDescription(),
            $this->createPostAddress($command->getAddress()),
            $command->getInvitations(),
            $command->getBeginAt(),
            $command->getFinishAt(),
            $command->getTimeZone()
        );
    }

    public function updateFromEventCommand(Event $event, EventCommand $command): Event
    {
        $event->update(
            $command->getName(),
            $command->getCategory(),
            $command->getDescription(),
            $this->createPostAddress($command->getAddress()),
            $command->getTimeZone(),
            $command->getBeginAt()->format(\DATE_ATOM),
            $command->getFinishAt()->format(\DATE_ATOM),
            $command->getCapacity(),
            $command->isForLegislatives()
        );

        $this->referentTagManager->assignReferentLocalTags($event);

        return $event;
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
            $command->getTimeZone()
        );

        $this->referentTagManager->assignReferentLocalTags($citizenAction);

        return $citizenAction;
    }

    public function updateFromCitizenActionCommand(CitizenActionCommand $command, CitizenAction $citizenAction): void
    {
        $citizenAction->update(
            $command->getName(),
            $command->getCategory(),
            $command->getDescription(),
            $this->createPostAddress($command->getAddress()),
            $command->getBeginAt(),
            $command->getFinishAt(),
            $command->getTimeZone()
        );

        $this->referentTagManager->assignReferentLocalTags($citizenAction);
    }

    private function createPostAddress(Address $address): PostAddress
    {
        return $this->addressFactory->createFromAddress($address);
    }
}
