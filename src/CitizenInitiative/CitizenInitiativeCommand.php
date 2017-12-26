<?php

namespace AppBundle\CitizenInitiative;

use AppBundle\Address\Address;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\CitizenInitiative;
use AppBundle\Entity\CitizenInitiativeCategory;
use AppBundle\Entity\CoachingRequest;
use AppBundle\Entity\SkillTrait;
use AppBundle\Event\BaseEventCommand;
use Doctrine\Common\Collections\ArrayCollection;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;

class CitizenInitiativeCommand extends BaseEventCommand
{
    use SkillTrait;

    private $citizenInitiative;

    private $interests = [];

    private $expertAssistanceNeeded;

    private $skills;

    private $coachingRequested;

    /**
     * @Assert\Regex("/^\d+$/", message="citizen_initiative.invalid_capacity")
     */
    private $capacity;

    /**
     * @var CoachingRequest
     */
    private $coachingRequest;

    private $place;

    public function __construct(
        ?Adherent $author,
        UuidInterface $uuid = null,
        Address $address = null,
        int $capacity = null,
        \DateTimeInterface $beginAt = null,
        \DateTimeInterface $finishAt = null,
        ?string $place = null,
        CitizenInitiative $initiative = null
    ) {
        parent::__construct($author, $uuid, $address, $beginAt, $finishAt, $initiative);

        $this->capacity = $capacity;
        $this->skills = new ArrayCollection();
        $this->place = $place;
    }

    public static function createFromCitizenInitiative(CitizenInitiative $citizenInitiative): self
    {
        $command = new self(
            $citizenInitiative->getOrganizer(),
            $citizenInitiative->getUuid(),
            self::getAddressModelFromEvent($citizenInitiative),
            $citizenInitiative->getCapacity(),
            $citizenInitiative->getBeginAt(),
            $citizenInitiative->getFinishAt(),
            $citizenInitiative->getPlace(),
            $citizenInitiative
        );

        $command->category = $citizenInitiative->getCategory();
        $command->expertAssistanceNeeded = $citizenInitiative->isExpertAssistanceNeeded();
        $command->coachingRequested = $citizenInitiative->isCoachingRequested();
        $command->coachingRequest = $citizenInitiative->getCoachingRequest();
        $command->interests = $citizenInitiative->getInterests();
        $command->skills = $citizenInitiative->getSkills();

        return $command;
    }

    public function setCitizenInitiative(CitizenInitiative $citizenInitiative): void
    {
        $this->citizenInitiative = $citizenInitiative;
    }

    public function getCitizenInitiative(): ?CitizenInitiative
    {
        return $this->citizenInitiative;
    }

    public function getInterests(): ?array
    {
        return $this->interests;
    }

    public function getInterestsAsJson(): ?string
    {
        return \GuzzleHttp\json_encode($this->interests, JSON_PRETTY_PRINT);
    }

    public function setInterests(?array $interests): void
    {
        $this->interests = $interests;
    }

    public function isExpertAssistanceNeeded(): ?bool
    {
        return $this->expertAssistanceNeeded;
    }

    public function setExpertAssistanceNeeded(bool $expertAssistanceNeeded): void
    {
        $this->expertAssistanceNeeded = $expertAssistanceNeeded;
    }

    public function isCoachingRequested(): ?bool
    {
        return $this->coachingRequested;
    }

    public function setCoachingRequested(?bool $coachingRequested): void
    {
        $this->coachingRequested = $coachingRequested;
    }

    public function setCoachingRequest(?CoachingRequest $coachingRequest): void
    {
        $this->coachingRequest = $coachingRequest;
    }

    public function getCoachingRequest(): ?CoachingRequest
    {
        return $this->coachingRequest;
    }

    public function getCapacity(): ?int
    {
        return null !== $this->capacity ? (int) $this->capacity : null;
    }

    public function setCapacity(?int $capacity): void
    {
        $this->capacity = $capacity;
    }

    public function getPlace(): ?string
    {
        return $this->place;
    }

    public function setPlace(?string $place): void
    {
        $this->place = $place;
    }

    protected function getCategoryClass(): string
    {
        return CitizenInitiativeCategory::class;
    }
}
