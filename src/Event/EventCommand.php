<?php

namespace AppBundle\Event;

use AppBundle\Address\Address;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;
use AppBundle\Entity\Event;
use AppBundle\Entity\EventCategory;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;

class EventCommand extends BaseEventCommand
{
    /**
     * @Assert\Regex("/^\d+$/", message="committee.event.invalid_capacity")
     */
    private $capacity;

    /**
     * @var bool
     */
    private $isForLegislatives;

    /**
     * @var Committee|null
     */
    private $committee;

    public function __construct(
        ?Adherent $author,
        Committee $committee = null,
        UuidInterface $uuid = null,
        Address $address = null,
        \DateTimeInterface $beginAt = null,
        \DateTimeInterface $finishAt = null,
        bool $isForLegislatives = false,
        Event $event = null
    ) {
        parent::__construct($author, $uuid, $address, $beginAt, $finishAt, $event);

        $this->committee = $committee;
        $this->isForLegislatives = $isForLegislatives;
    }

    public static function createFromEvent(Event $event): self
    {
        $command = new self(
            $event->getOrganizer(),
            $event->getCommittee(),
            $event->getUuid(),
            self::getAddressModelFromEvent($event),
            $event->getBeginAt(),
            $event->getFinishAt(),
            $event->isForLegislatives(),
            $event
        );

        $command->category = $event->getCategory();
        $command->capacity = $event->getCapacity();
        $command->isForLegislatives = $event->isForLegislatives();

        return $command;
    }

    public function getCapacity(): ?int
    {
        return null !== $this->capacity ? (int) $this->capacity : null;
    }

    public function setCapacity($capacity): void
    {
        if (null !== $capacity) {
            $capacity = (int) $capacity;
        }

        $this->capacity = $capacity;
    }

    public function isForLegislatives(): bool
    {
        return $this->isForLegislatives;
    }

    public function setIsForLegislatives(bool $isForLegislatives): void
    {
        $this->isForLegislatives = $isForLegislatives;
    }

    public function getCommittee(): ?Committee
    {
        return $this->committee;
    }

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    protected function getCategoryClass(): string
    {
        return EventCategory::class;
    }
}
