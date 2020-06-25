<?php

namespace App\Entity\VotingPlatform\Designation;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\VotingPlatform\Designation\DesignationTypeEnum;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\VotingPlatform\DesignationRepository")
 *
 * @Algolia\Index(autoIndex=false)
 */
class Designation
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;

    /**
     * @var string|null
     *
     * @ORM\Column
     *
     * @Assert\NotBlank
     */
    private $type;

    /**
     * @var string[]
     *
     * @ORM\Column(type="simple_array")
     *
     * @Assert\NotBlank
     * @Assert\Count(min=1)
     */
    private $zones = [];

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime")
     *
     * @Assert\NotBlank
     */
    private $candidacyStartDate;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime")
     *
     * @Assert\NotBlank
     */
    private $candidacyEndDate;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime")
     *
     * @Assert\NotBlank
     */
    private $voteStartDate;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime")
     *
     * @Assert\NotBlank
     */
    private $voteEndDate;

    /**
     * @var int
     *
     * @ORM\Column(type="smallint", options={"unsigned": true})
     *
     * @Assert\NotBlank
     * @Assert\GreaterThanOrEqual(0)
     */
    private $resultDisplayDelay = 14;

    /**
     * Duration of the additional round in day
     *
     * @var int
     *
     * @ORM\Column(type="smallint", options={"unsigned": true})
     *
     * @Assert\NotBlank
     * @Assert\GreaterThan(0)
     */
    private $additionalRoundDuration = 5;

    /**
     * @var int
     *
     * @ORM\Column(type="smallint", options={"unsigned": true})
     *
     * @Assert\NotBlank
     * @Assert\GreaterThan(0)
     */
    private $lockPeriodThreshold = 3;

    public function __construct(UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): void
    {
        $this->type = $type;
    }

    public function getZones(): array
    {
        return $this->zones;
    }

    public function setZones(array $zones): void
    {
        $this->zones = $zones;
    }

    public function getCandidacyStartDate(): ?\DateTime
    {
        return $this->candidacyStartDate;
    }

    public function setCandidacyStartDate(?\DateTime $candidacyStartDate): void
    {
        $this->candidacyStartDate = $candidacyStartDate;
    }

    public function getCandidacyEndDate(): ?\DateTime
    {
        return $this->candidacyEndDate;
    }

    public function setCandidacyEndDate(?\DateTime $candidacyEndDate): void
    {
        $this->candidacyEndDate = $candidacyEndDate;
    }

    public function getVoteStartDate(): ?\DateTime
    {
        return $this->voteStartDate;
    }

    public function setVoteStartDate(?\DateTime $voteStartDate): void
    {
        $this->voteStartDate = $voteStartDate;
    }

    public function getVoteEndDate(): ?\DateTime
    {
        return $this->voteEndDate;
    }

    public function setVoteEndDate(?\DateTime $voteEndDate): void
    {
        $this->voteEndDate = $voteEndDate;
    }

    public function getResultDisplayDelay(): int
    {
        return $this->resultDisplayDelay;
    }

    public function setResultDisplayDelay(int $resultDisplayDelay): void
    {
        $this->resultDisplayDelay = $resultDisplayDelay;
    }

    public function getAdditionalRoundDuration(): int
    {
        return $this->additionalRoundDuration;
    }

    public function setAdditionalRoundDuration(int $additionalRoundDuration): void
    {
        $this->additionalRoundDuration = $additionalRoundDuration;
    }

    public function getTitle(): string
    {
        switch ($this->type) {
            case DesignationTypeEnum::COMMITTEE_ADHERENT:
                return 'Désignation du binôme d’adhérents siégeant au Conseil territorial';
        }

        return '';
    }

    public function getLockPeriodThreshold(): int
    {
        return $this->lockPeriodThreshold;
    }

    public function setLockPeriodThreshold(int $lockPeriodThreshold): void
    {
        $this->lockPeriodThreshold = $lockPeriodThreshold;
    }
}
