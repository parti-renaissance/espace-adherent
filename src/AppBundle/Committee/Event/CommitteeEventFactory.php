<?php

namespace AppBundle\Committee\Event;

use AppBundle\Address\PostAddressFactory;
use AppBundle\Entity\CommitteeEvent;
use Ramsey\Uuid\Uuid;

class CommitteeEventFactory
{
    private $addressFactory;

    public function __construct(PostAddressFactory $addressFactory = null)
    {
        $this->addressFactory = $addressFactory ?: new PostAddressFactory();
    }

    public function createFromArray(array $data): CommitteeEvent
    {
        foreach (['uuid', 'author', 'committee', 'name', 'category', 'description', 'address', 'begin_at', 'finish_at', 'capacity'] as $key) {
            if (empty($data[$key])) {
                throw new \InvalidArgumentException(sprintf('Key "%s" is missing or has an empty value.', $key));
            }
        }

        $uuid = Uuid::fromString($data['uuid']);
        $author = Uuid::fromString($data['author']);

        return new CommitteeEvent(
            $uuid,
            $author,
            $data['committee'],
            $data['name'],
            $data['category'],
            $data['description'],
            $data['address'],
            $data['begin_at'],
            $data['finish_at'],
            $data['capacity']
        );
    }

    public function createFromCommitteeEventCommand(CommitteeEventCommand $command): CommitteeEvent
    {
        return new CommitteeEvent(
            $command->getUuid(),
            $command->getAuthor()->getUuid(),
            $command->getCommittee(),
            $command->getName(),
            $command->getCategory(),
            $command->getDescription(),
            $this->addressFactory->createFromAddress($command->getAddress()),
            $command->getBeginAt()->format(DATE_ATOM),
            $command->getFinishAt()->format(DATE_ATOM),
            $command->getCapacity()
        );
    }
}
