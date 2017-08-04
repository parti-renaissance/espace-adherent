<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\EventRepository")
 */
class CitizenInitiative extends EventBase
{
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
        EventCategory $category,
        string $description,
        PostAddress $address,
        \DateTime $beginAt,
        \DateTime $finishAt,
        bool $expertAssistanceNeeded = false,
        string $expertAssistanceDescription = '',
        bool $coachingRequested = false,
        CoachingRequest $coachingRequest = null,
        array $interests = [],
        \DateTime $createdAt = null,
        int $participantsCount = 0
    ) {
        $this->uuid = $uuid;
        $this->organizer = $organizer;
        $this->setName($name);
        $this->category = $category;
        $this->description = $description;
        $this->postAddress = $address;
        $this->participantsCount = $participantsCount;
        $this->beginAt = $beginAt;
        $this->finishAt = $finishAt;
        $this->createdAt = $this->updatedAt = $createdAt ?: new \DateTimeImmutable('now');
        $this->expertAssistanceNeeded = $expertAssistanceNeeded;
        $this->expertAssistanceDescription = $expertAssistanceDescription;
        $this->coachingRequested = $coachingRequested;
        $this->coachingRequest = $coachingRequest;
        $this->interests = $interests;
        $this->status = self::STATUS_SCHEDULED;
        $this->skills = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->name ?: '';
    }

    public function update(
        string $name,
        EventCategory $category,
        string $description,
        PostAddress $address,
        string $beginAt,
        string $finishAt
    ) {
        $this->setName($name);
        $this->category = $category;
        $this->beginAt = new \DateTime($beginAt);
        $this->finishAt = new \DateTime($finishAt);
        $this->description = $description;

        if (!$this->postAddress->equals($address)) {
            $this->postAddress = $address;
        }
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

    public function addSkill(Skill $skill)
    {
        if (!$this->skills->contains($skill)) {
            $this->skills->add($skill);
            $skill->addCitizenInitiative($this);
        }
    }

    public function replaceSkill(Skill $actual, Skill $new): void
    {
        $this->removeSkill($actual);
        $this->addSkill($new);
    }

    public function removeSkill(Skill $skill)
    {
        if ($this->skills->contains($skill)) {
            $this->skills->removeElement($skill);
        }
    }

    /**
     * @return Skill[]|Collection|iterable
     */
    public function getSkills(): iterable
    {
        return $this->skills;
    }
}
