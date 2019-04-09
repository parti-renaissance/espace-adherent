<?php

namespace AppBundle\Event;

use AppBundle\Address\Address;
use AppBundle\Address\GeoCoder;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;
use AppBundle\Entity\Event;
use AppBundle\Entity\EventCategory;
use AppBundle\Validator\DateRange;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @DateRange(
 *     startDateField="beginAt",
 *     endDateField="finishAt",
 *     interval="3 days",
 *     message="committee.event.invalid_finish_date"
 * )
 */
class EventCommand extends BaseEventCommand
{
    /**
     * @Assert\GreaterThan("0", message="committee.event.invalid_capacity")
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
        Event $event = null,
        string $timezone = GeoCoder::DEFAULT_TIME_ZONE
    ) {
        parent::__construct($author, $uuid, $address, $beginAt, $finishAt, $event, $timezone);

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
            $event,
            $event->getTimeZone()
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
