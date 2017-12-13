<?php

namespace AppBundle\CitizenAction;

use AppBundle\Address\Address;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\CitizenAction;
use AppBundle\Entity\CitizenActionCategory;
use AppBundle\Entity\CitizenProject;
use AppBundle\Event\BaseEventCommand;
use Ramsey\Uuid\UuidInterface;

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
