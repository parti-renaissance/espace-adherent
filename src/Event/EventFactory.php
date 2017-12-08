<?php

namespace AppBundle\Event;

use AppBundle\Address\Address;
use AppBundle\Address\PostAddressFactory;
use AppBundle\CitizenAction\CitizenActionCommand;
use AppBundle\CitizenInitiative\CitizenInitiativeCommand;
use AppBundle\Entity\CitizenInitiative;
use AppBundle\Entity\Event;
use AppBundle\Entity\CitizenAction;
use AppBundle\Entity\PostAddress;
use Ramsey\Uuid\Uuid;

class EventFactory
{
    private $addressFactory;

    public function __construct(PostAddressFactory $addressFactory = null)
    {
        $this->addressFactory = $addressFactory ?: new PostAddressFactory();
    }

    public function createFromArray(array $data): Event
    {
        foreach (['uuid', 'committee', 'name', 'category', 'description', 'address', 'begin_at', 'finish_at', 'capacity'] as $key) {
            if (empty($data[$key])) {
                throw new \InvalidArgumentException(sprintf('Key "%s" is missing or has an empty value.', $key));
            }
        }

        $uuid = Uuid::fromString($data['uuid']);

        return new Event(
            $uuid,
            $data['organizer'] ?? null,
            $data['committee'],
            $data['name'],
            $data['category'],
            $data['description'],
            $data['address'],
            $data['begin_at'],
            $data['finish_at'],
            $data['capacity'],
            $data['is_for_legislatives'] ?? false
        );
    }

    public function createCitizenInitiativeFromArray(array $data): CitizenInitiative
    {
        foreach (['uuid', 'organizer', 'name', 'category', 'description', 'address', 'begin_at', 'finish_at',
                     'expert_assistance_needed', 'coaching_requested', 'capacity', 'place', ] as $key) {
            if (!array_key_exists($key, $data)) {
                throw new \InvalidArgumentException(sprintf('Key "%s" is missing.', $key));
            }
        }

        $uuid = Uuid::fromString($data['uuid']);

        return new CitizenInitiative(
            $uuid,
            $data['organizer'],
            $data['name'],
            $data['category'],
            $data['description'],
            $data['address'],
            $data['begin_at'],
            $data['finish_at'],
            $data['expert_assistance_needed'],
            $data['coaching_requested'],
            null,
            [],
            $data['capacity'],
            null,
            0,
            $data['place']
        );
    }

    public function createCitizenActionFromArray(array $data): CitizenAction
    {
        foreach (['uuid', 'organizer', 'citizen_project', 'name', 'category', 'description', 'address', 'begin_at', 'finish_at'] as $key) {
            if (!array_key_exists($key, $data)) {
                throw new \InvalidArgumentException(sprintf('Key "%s" is missing.', $key));
            }
        }

        $uuid = Uuid::fromString($data['uuid']);

        return new CitizenAction(
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
    }

    public function createFromCitizenInitiativeCommand(CitizenInitiativeCommand $command): CitizenInitiative
    {
        $initiative = new CitizenInitiative(
            $command->getUuid(),
            $command->getAuthor(),
            $command->getName(),
            $command->getCategory(),
            $command->getDescription(),
            $this->createPostAddress($command->getAddress()),
            $command->getBeginAt(),
            $command->getFinishAt(),
            $command->isExpertAssistanceNeeded(),
            $command->isCoachingRequested(),
            $command->getCoachingRequest(),
            $command->getInterests(),
            $command->getCapacity(),
            null,
            0,
            $command->getPlace()
        );

        $initiative->setSkills($command->getSkills());

        return $initiative;
    }

    public function createFromEventCommand(EventCommand $command): Event
    {
        return new Event(
            $command->getUuid(),
            $command->getAuthor(),
            $command->getCommittee(),
            $command->getName(),
            $command->getCategory(),
            $command->getDescription(),
            $this->createPostAddress($command->getAddress()),
            $command->getBeginAt()->format(DATE_ATOM),
            $command->getFinishAt()->format(DATE_ATOM),
            $command->getCapacity(),
            $command->isForLegislatives()
        );
    }

    public function updateFromEventCommand(Event $event, EventCommand $command): Event
    {
        $event->update(
            $command->getName(),
            $command->getCategory(),
            $command->getDescription(),
            $this->createPostAddress($command->getAddress()),
            $command->getBeginAt()->format(DATE_ATOM),
            $command->getFinishAt()->format(DATE_ATOM),
            $command->getCapacity(),
            $command->isForLegislatives()
        );

        return $event;
    }

    public function updateFromCitizenInitiativeCommand(CitizenInitiative $initiative, CitizenInitiativeCommand $command): CitizenInitiative
    {
        $initiative->update(
            $command->getName(),
            $command->getCategory(),
            $command->getDescription(),
            $this->createPostAddress($command->getAddress()),
            $command->getBeginAt(),
            $command->getFinishAt(),
            $command->isExpertAssistanceNeeded(),
            $command->isCoachingRequested(),
            $command->getCoachingRequest(),
            $command->getInterests(),
            $command->getCapacity(),
            $command->getSkills(),
            $command->getPlace()
        );

        return $initiative;
    }

    public function createFromCitizenActionCommand(CitizenActionCommand $command): CitizenAction
    {
        return new CitizenAction(
            $command->getUuid(),
            $command->getAuthor(),
            $command->getCitizenProject(),
            $command->getName(),
            $command->getCategory(),
            $command->getDescription(),
            $this->createPostAddress($command->getAddress()),
            $command->getBeginAt(),
            $command->getFinishAt()
        );
    }

    private function createPostAddress(Address $address): PostAddress
    {
        return $this->addressFactory->createFromAddress($address);
    }
}
