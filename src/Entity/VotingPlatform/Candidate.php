<?php

namespace AppBundle\Entity\VotingPlatform;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use AppBundle\Entity\EntityIdentityTrait;
use AppBundle\ValueObject\Genders;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity
 *
 * @ORM\Table(name="voting_platform_candidate")
 *
 * @Algolia\Index(autoIndex=false)
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
     * @ORM\Column(nullable=true)
     */
    private $imagePath;

    /**
     * @var CandidateGroup
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\VotingPlatform\CandidateGroup", inversedBy="candidates")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $candidateGroup;

    public function __construct(string $firstName, string $lastName, string $gender, UuidInterface $uuid = null)
    {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->gender = $gender;

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

    public function isWoman(): bool
    {
        return Genders::FEMALE === $this->gender;
    }

    public function isMan(): bool
    {
        return Genders::MALE === $this->gender;
    }

    public function getBiography(): ?string
    {
        return $this->biography;
    }

    public function setBiography(?string $biography): void
    {
        $this->biography = $biography;
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
}
