<?php

namespace AppBundle\CitizenInitiative;

use AppBundle\Address\Address;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\CitizenInitiative;
use AppBundle\Entity\CoachingRequest;
use AppBundle\Entity\EventCategory;
use AppBundle\Entity\SkillTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

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
     * @Assert\NotBlank
     * @Assert\DateTime
     */
    private $beginAt;

    /**
     * @Assert\NotBlank
     * @Assert\DateTime
     */
    private $finishAt;

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

    /**
     * @Assert\Length(max=250)
     */
    private $expertAssistanceDescription;

    private $skills;

    private $coachingRequested;

    /**
     * @var CoachingRequest
     */
    private $coachingRequest;

    public function __construct(
        Adherent $author = null,
        UuidInterface $uuid = null,
        Address $address = null,
        \DateTimeInterface $beginAt = null,
        \DateTimeInterface $finishAt = null
    ) {
        $this->uuid = $uuid ?: Uuid::uuid4();
        $this->author = $author;
        $this->address = $address ?: new Address();
        $this->beginAt = $beginAt ?: new \DateTime(date('Y-m-d 00:00:00'));
        $this->finishAt = $finishAt ?: new \DateTime(date('Y-m-d 23:59:59'));
        $this->skills = new ArrayCollection();
    }

    public static function createFromCitizenInitiative(CitizenInitiative $citizenInitiative): self
    {
        $command = new self(
            $citizenInitiative->getOrganizer(),
            $citizenInitiative->getUuid(),
            Address::createFromAddress($citizenInitiative->getPostAddressModel()),
            $citizenInitiative->getBeginAt(),
            $citizenInitiative->getFinishAt()
        );

        $command->name = $citizenInitiative->getName();
        $command->category = $citizenInitiative->getCategory();
        $command->description = $citizenInitiative->getDescription();

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

    public function getCategory(): ?EventCategory
    {
        return $this->category;
    }

    public function setCategory(?EventCategory $category = null): void
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

    public function getBeginAt(): ?\DateTimeInterface
    {
        return $this->beginAt;
    }

    public function setBeginAt(\DateTime $beginAt): void
    {
        $this->beginAt = $beginAt;
    }

    public function getFinishAt(): ?\DateTimeInterface
    {
        return $this->finishAt;
    }

    public function setFinishAt(\DateTime $finishAt): void
    {
        $this->finishAt = $finishAt;
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

    /**
     * @Assert\Callback
     */
    public static function validateDateRange(self $command, ExecutionContextInterface $context): void
    {
        $beginAt = $command->getBeginAt();
        $finishAt = $command->getFinishAt();

        if (!$beginAt instanceof \DateTimeInterface || !$finishAt instanceof \DateTimeInterface) {
            return;
        }

        if ($finishAt <= $beginAt) {
            $context
                ->buildViolation('committee.citizenInitiative.invalid_date_range')
                ->atPath('finishAt')
                ->addViolation();
        }
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

    public function setExpertAssistanceNeeded(array $expertAssistanceNeeded): void
    {
        $this->expertAssistanceNeeded = $expertAssistanceNeeded ? $expertAssistanceNeeded[0] : false;
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

    public function getExpertAssistanceDescription(): ?string
    {
        return $this->expertAssistanceDescription;
    }

    public function setExpertAssistanceDescription(?string $expertAssistanceDescription): void
    {
        $this->expertAssistanceDescription = $expertAssistanceDescription;
    }
}
