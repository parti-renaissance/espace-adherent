<?php

namespace AppBundle\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CitizenInitiativeRepository")
 */
class CitizenInitiative extends BaseEvent
{
    use SkillTrait;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\CitizenInitiativeCategory")
     *
     * @Algolia\Attribute
     */
    private $citizenInitiativeCategory;

    /**
     * @ORM\Column(type="json_array")
     */
    private $interests = [];

    /**
     * @ORM\Column(type="boolean", options={"default": 0})
     */
    private $expertAssistanceNeeded = false;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true, nullable=true)
     *
     * @Assert\Length(max=250)
     */
    private $expertAssistanceDescription;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $expertFound = false;

    /**
     * @var Skill[]|Collection
     *
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Skill", inversedBy="citizenInitiatives", cascade={"persist"})
     * @ORM\JoinTable(
     *     name="citizen_initiative_skills",
     *     joinColumns={
     *         @ORM\JoinColumn(name="citizen_initiative_id", referencedColumnName="id", onDelete="CASCADE")
     *     },
     *     inverseJoinColumns={
     *         @ORM\JoinColumn(name="skill_id", referencedColumnName="id", onDelete="CASCADE")
     *     }
     * )
     */
    private $skills;

    /**
     * @ORM\Column(type="boolean", options={"default": 0})
     */
    private $coachingRequested;

    /**
     * @ORM\Embedded(class="CoachingRequest", columnPrefix="coaching_request_")
     *
     * @var CoachingRequest
     */
    private $coachingRequest;

    public function __construct(
        UuidInterface $uuid,
        Adherent $organizer,
        string $name,
        CitizenInitiativeCategory $citizenInitiativeCategory,
        string $description,
        PostAddress $address,
        \DateTime $beginAt,
        \DateTime $finishAt,
        bool $expertAssistanceNeeded = false,
        string $expertAssistanceDescription = '',
        bool $coachingRequested = false,
        CoachingRequest $coachingRequest = null,
        array $interests = [],
        int $capacity = null,
        \DateTime $createdAt = null,
        int $participantsCount = 0
    ) {
        $this->uuid = $uuid;
        $this->organizer = $organizer;
        $this->setName($name);
        $this->citizenInitiativeCategory = $citizenInitiativeCategory;
        $this->description = $description;
        $this->postAddress = $address;
        $this->participantsCount = $participantsCount;
        $this->beginAt = $beginAt;
        $this->finishAt = $finishAt;
        $this->createdAt = $createdAt ?: new \DateTimeImmutable('now');
        $this->updatedAt = $createdAt ?: new \DateTime('now');
        $this->expertAssistanceNeeded = $expertAssistanceNeeded;
        $this->expertAssistanceDescription = $expertAssistanceDescription;
        $this->coachingRequested = $coachingRequested;
        $this->coachingRequest = $coachingRequest;
        $this->interests = $interests;
        $this->status = self::STATUS_SCHEDULED;
        $this->skills = new ArrayCollection();
        $this->capacity = $capacity;
    }

    public function __toString(): string
    {
        return $this->name ?: '';
    }

    public function update(
        string $name,
        CitizenInitiativeCategory $citizenInitiativeCategory,
        string $description,
        PostAddress $address,
        \DateTime $beginAt,
        \DateTime $finishAt,
        bool $expertAssistanceNeeded = false,
        string $expertAssistanceDescription = '',
        bool $coachingRequested = false,
        CoachingRequest $coachingRequest = null,
        array $interests = [],
        int $capacity = null,
        $skills = null
    ) {
        $this->setName($name);
        $this->citizenInitiativeCategory = $citizenInitiativeCategory;
        $this->description = $description;
        $this->beginAt = $beginAt;
        $this->finishAt = $finishAt;
        $this->expertAssistanceNeeded = $expertAssistanceNeeded;
        $this->expertAssistanceDescription = $expertAssistanceDescription;
        $this->coachingRequested = $coachingRequested;
        $this->coachingRequest = $coachingRequest;
        $this->setInterests($interests);
        $this->capacity = $capacity;
        $this->skills = $skills;

        if (!$this->postAddress->equals($address)) {
            $this->postAddress = $address;
        }
    }

    public function getCategory(): CitizenInitiativeCategory
    {
        return $this->citizenInitiativeCategory;
    }

    public function setCategory(CitizenInitiativeCategory $category): CitizenInitiative
    {
        $this->citizenInitiativeCategory = $category;

        return $this;
    }

    public function getInterests(): array
    {
        return $this->interests;
    }

    public function getInterestsAsJson(): string
    {
        return \GuzzleHttp\json_encode($this->interests, JSON_PRETTY_PRINT);
    }

    public function setInterests(array $interests): void
    {
        $this->interests = $interests;
    }

    public function isExpertAssistanceNeeded(): bool
    {
        return $this->expertAssistanceNeeded;
    }

    public function setExpertAssistanceNeeded(bool $expertAssistanceNeeded): void
    {
        $this->expertAssistanceNeeded = $expertAssistanceNeeded;
    }

    public function isExpertFound(): bool
    {
        return $this->expertFound;
    }

    public function setExpertFound(bool $expertFound): void
    {
        $this->expertFound = $expertFound;
    }

    public function isCoachingRequested(): bool
    {
        return $this->coachingRequested;
    }

    public function setCoachingRequested(bool $coachingRequested): void
    {
        $this->coachingRequested = $coachingRequested;
    }

    public function setCoachingRequest(CoachingRequest $coachingRequest): void
    {
        $this->coachingRequest = $coachingRequest;
    }

    public function getExpertAssistanceDescription(): ?string
    {
        return $this->expertAssistanceDescription;
    }

    public function setExpertAssistanceDescription(?string $expertAssistanceDescription): void
    {
        $this->expertAssistanceDescription = $expertAssistanceDescription;
    }

    public function getCoachingRequest(): CoachingRequest
    {
        return $this->coachingRequest;
    }

    public function getProblemDescription(): ?string
    {
        return $this->coachingRequest->getProblemDescription();
    }

    public function getProposedSolution(): ?string
    {
        return $this->coachingRequest->getProposedSolution();
    }

    public function getRequiredMeans(): ?string
    {
        return $this->coachingRequest->getRequiredMeans();
    }

    public function getType(): string
    {
        return self::CITIZEN_INITIATIVE_TYPE;
    }
}
