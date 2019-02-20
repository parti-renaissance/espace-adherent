<?php

namespace AppBundle\InstitutionalEvent;

use AppBundle\Address\Address;
use AppBundle\Address\GeoCoder;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\BaseEventCategory;
use AppBundle\Entity\InstitutionalEvent;
use AppBundle\Entity\InstitutionalEventCategory;
use AppBundle\Event\BaseEventCommand;
use AppBundle\Validator\DateRange;
use Ramsey\Uuid\UuidInterface;

/**
 * @DateRange(
 *     startDateField="beginAt",
 *     endDateField="finishAt",
 *     interval="3 days",
 *     message="committee.event.invalid_finish_date"
 * )
 */
class InstitutionalEventCommand extends BaseEventCommand
{
    /**
     * @var InstitutionalEvent|null
     */
    protected $event;

    public function __construct(
        ?Adherent $author,
        UuidInterface $uuid = null,
        Address $address = null,
        \DateTimeInterface $beginAt = null,
        \DateTimeInterface $finishAt = null,
        InstitutionalEvent $event = null,
        string $timezone = GeoCoder::DEFAULT_TIME_ZONE
    ) {
        parent::__construct($author, $uuid, $address, $beginAt, $finishAt, $event, $timezone);
    }

    public static function createFromInstitutionalEvent(InstitutionalEvent $event): self
    {
        $command = new self(
            $event->getOrganizer(),
            $event->getUuid(),
            self::getAddressModelFromEvent($event),
            $event->getBeginAt(),
            $event->getFinishAt(),
            $event,
            $event->getTimeZone()
        );

        $command->category = $event->getCategory();

        return $command;
    }

    public function getEvent(): ?InstitutionalEvent
    {
        return $this->event;
    }

    /**
     * @return InstitutionalEventCategory|null
     */
    public function getCategory(): ?BaseEventCategory
    {
        return parent::getCategory();
    }

    protected function getCategoryClass(): string
    {
        return InstitutionalEventCategory::class;
    }
}
