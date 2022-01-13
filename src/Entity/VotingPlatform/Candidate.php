<?php

namespace App\Entity\VotingPlatform;

use App\Entity\Adherent;
use App\Entity\EntityIdentityTrait;
use App\ValueObject\Genders;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="voting_platform_candidate")
 */
class Candidate
{
    use EntityIdentityTrait;

    /**
     * @var string
     *
     * @ORM\Column
     */
    private $firstName;

    /**
     * @var string
     *
     * @ORM\Column
     */
    private $lastName;

    /**
     * @var string
     *
     * @ORM\Column
     */
    private $gender;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $biography;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $faithStatement;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     */
    private $imagePath;

    /**
     * @var CandidateGroup
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\VotingPlatform\CandidateGroup", inversedBy="candidates")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $candidateGroup;

    /**
     * @var Adherent
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Adherent")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $adherent;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $additionallyElected = false;

    public function __construct(
        string $firstName,
        string $lastName,
        string $gender,
        Adherent $adherent = null,
        UuidInterface $uuid = null
    ) {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->gender = $gender;
        $this->adherent = $adherent;

        $this->uuid = $uuid ?? Uuid::uuid4();
    }

    public function setCandidateGroup(CandidateGroup $candidateGroup): void
    {
        $this->candidateGroup = $candidateGroup;
    }

    public function getFullName(): string
    {
        return sprintf('%s %s', $this->firstName, $this->lastName);
    }

    public function isFemale(): bool
    {
        return Genders::FEMALE === $this->gender;
    }

    public function getGender(): string
    {
        return $this->gender;
    }

    public function getBiography(): ?string
    {
        return $this->biography;
    }

    public function setBiography(?string $biography): void
    {
        $this->biography = $biography;
    }

    public function getFaithStatement(): ?string
    {
        return $this->faithStatement;
    }

    public function setFaithStatement(?string $faithStatement): void
    {
        $this->faithStatement = $faithStatement;
    }

    public function getImagePath(): ?string
    {
        return $this->imagePath;
    }

    public function setImagePath(?string $imagePath): void
    {
        $this->imagePath = $imagePath;
    }

    public function hasPhoto(): bool
    {
        return !empty($this->imagePath);
    }

    public function hasBiography(): bool
    {
        return !empty($this->biography);
    }

    public function getInitials(): string
    {
        return mb_strtoupper(mb_substr($this->firstName, 0, 1).mb_substr($this->lastName, 0, 1));
    }

    public function isAdditionallyElected(): bool
    {
        return $this->additionallyElected;
    }

    public function setAdditionallyElected(bool $elected): void
    {
        $this->additionallyElected = $elected;
    }

    public function getAdherent(): Adherent
    {
        return $this->adherent;
    }
}
