<?php

namespace App\Event;

use App\Address\Address;
use App\Address\GeoCoder;
use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\Event\Event;
use App\Entity\Event\EventCategory;
use App\Validator\DateRange;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[DateRange(
    startDateField: 'beginAt',
    endDateField: 'finishAt',
    interval: '3 days',
    messageDate: 'committee.event.invalid_finish_date'
)]
class EventCommand extends BaseEventCommand
{
    #[Assert\GreaterThan('0', message: 'committee.event.invalid_capacity')]
    private $capacity;

    /**
     * @var Committee|null
     */
    private $committee;

    /**
     * @var bool
     */
    private $private = false;

    /**
     * @var bool
     */
    private $electoral = false;

    public function __construct(
        ?Adherent $author,
        ?Committee $committee = null,
        ?UuidInterface $uuid = null,
        ?Address $address = null,
        ?\DateTimeInterface $beginAt = null,
        ?\DateTimeInterface $finishAt = null,
        ?Event $event = null,
        string $timezone = GeoCoder::DEFAULT_TIME_ZONE,
        ?string $visioUrl = null,
    ) {
        parent::__construct($author, $uuid, $address, $beginAt, $finishAt, $event, $timezone, $visioUrl);

        $this->committee = $committee;
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
            $event,
            $event->getTimeZone(),
            $event->getVisioUrl()
        );

        $command->setMode($event->getMode());
        $command->setCategory($event->getCategory());
        $command->setCapacity($event->getCapacity());
        $command->setPrivate($event->isPrivate());
        $command->setElectoral($event->isElectoral());

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

    public function getCommittee(): ?Committee
    {
        return $this->committee;
    }

    protected function getCategoryClass(): string
    {
        return EventCategory::class;
    }

    public function isPrivate(): bool
    {
        return $this->private;
    }

    public function setPrivate(bool $private): void
    {
        $this->private = $private;
    }

    public function isElectoral(): bool
    {
        return $this->electoral;
    }

    public function setElectoral(bool $electoral): void
    {
        $this->electoral = $electoral;
    }
}
