<?php

namespace AppBundle\CitizenInitiative;

use AppBundle\Address\Address;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\CitizenInitiative;
use AppBundle\Entity\CitizenInitiativeCategory;
use AppBundle\Entity\CoachingRequest;
use AppBundle\Entity\SkillTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;

class CitizenInitiativeCommand
{
    use SkillTrait;

    private $uuid;

    /**
     * @Assert\NotBlank
     * @Assert\Length(min=5, max=100)
     */
    private $name;

    /**
     * @Assert\NotNull
     */
    private $category;

    /**
     * @Assert\NotBlank
     * @Assert\Length(min=10)
     */
    private $description;

    /**
     * @var Address
     *
     * @Assert\NotBlank
     * @Assert\Valid
     */
    private $address;

    private $author;
    private $citizenInitiative;

    private $interests = [];

    private $expertAssistanceNeeded;

    private $skills;

    private $coachingRequested;

    /**
     * @var CoachingRequest
     */
    private $coachingRequest;

    private $place;

    public function __construct(
        Adherent $author = null,
        UuidInterface $uuid = null,
        Address $address = null
    ) {
        $this->uuid = $uuid ?: Uuid::uuid4();
        $this->author = $author;
        $this->address = $address ?: new Address();
        $this->skills = new ArrayCollection();
    }

    public static function createFromCitizenInitiative(CitizenInitiative $citizenInitiative): self
    {
        $command = new self(
            $citizenInitiative->getOrganizer(),
            $citizenInitiative->getUuid(),
            Address::createFromAddress($citizenInitiative->getPostAddressModel())
        );

        $command->name = $citizenInitiative->getName();
        $command->category = $citizenInitiative->getCategory();
        $command->description = $citizenInitiative->getDescription();
        $command->expertAssistanceNeeded = $citizenInitiative->isExpertAssistanceNeeded();
        $command->coachingRequested = $citizenInitiative->isCoachingRequested();
        $command->coachingRequest = $citizenInitiative->getCoachingRequest();
        $command->interests = $citizenInitiative->getInterests();
        $command->skills = $citizenInitiative->getSkills();
        $command->place = $citizenInitiative->getPlace();

        return $command;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getCategory(): ?CitizenInitiativeCategory
    {
        return $this->category;
    }

    public function setCategory(?CitizenInitiativeCategory $category = null): void
    {
        $this->category = $category;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function setAddress(Address $address): void
    {
        $this->address = $address;
    }

    public function getAddress(): ?Address
    {
        return $this->address;
    }

    public function getAuthor(): Adherent
    {
        return $this->author;
    }

    public function setCitizenInitiative(CitizenInitiative $citizenInitiative): void
    {
        $this->citizenInitiative = $citizenInitiative;
    }

    public function getCitizenInitiative(): ?CitizenInitiative
    {
        return $this->citizenInitiative;
    }

    public function getUuid(): UuidInterface
    {
        return $this->uuid;
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

    public function getPlace(): ? string
    {
        return $this->place;
    }

    public function setPlace(string $place): void
    {
        $this->place = $place;
    }
}
