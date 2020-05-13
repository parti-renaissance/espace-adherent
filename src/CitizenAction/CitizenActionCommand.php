<?php

namespace App\CitizenAction;

use App\Address\Address;
use App\Entity\Adherent;
use App\Entity\CitizenAction;
use App\Entity\CitizenActionCategory;
use App\Entity\CitizenProject;
use App\Event\BaseEventCommand;
use App\Validator\DateRange;
use Ramsey\Uuid\UuidInterface;

/**
 * @DateRange(
 *     startDateField="beginAt",
 *     endDateField="finishAt",
 *     interval="3 days",
 *     message="citizen_project.action.invalid_finish_date"
 * )
 */
class CitizenActionCommand extends BaseEventCommand
{
    /**
     * @var CitizenAction|null
     */
    private $citizenProject;

    public function __construct(
        ?Adherent $author,
        CitizenProject $project,
        UuidInterface $uuid = null,
        Address $address = null,
        \DateTimeInterface $beginAt = null,
        \DateTimeInterface $finishAt = null,
        CitizenAction $action = null
    ) {
        parent::__construct($author, $uuid, $address, $beginAt, $finishAt, $action);

        $this->citizenProject = $project;
    }

    public static function createFromCitizenAction(CitizenAction $action): self
    {
        $command = new self(
            $action->getOrganizer(),
            $action->getCitizenProject(),
            $action->getUuid(),
            self::getAddressModelFromEvent($action),
            $action->getBeginAt(),
            $action->getFinishAt(),
            $action
        );

        $command->category = $action->getCategory();
        $command->citizenProject = $action->getCitizenProject();

        return $command;
    }

    public function getCitizenProject(): CitizenProject
    {
        return $this->citizenProject;
    }

    protected function getCategoryClass(): string
    {
        return CitizenActionCategory::class;
    }
}
