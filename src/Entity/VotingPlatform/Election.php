<?php

namespace AppBundle\Entity\VotingPlatform;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use AppBundle\Entity\EntityIdentityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\VotingPlatform\ElectionRepository")
 *
 * @ORM\Table(name="voting_platform_election")
 *
 * @Algolia\Index(autoIndex=false)
 */
class Election
{
    use EntityIdentityTrait;

    /**
     * @var string
     *
     * @ORM\Column
     *
     * @Assert\NotBlank
     */
    private $title;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     *
     * @Assert\NotBlank
     */
    private $startDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     *
     * @Assert\NotBlank
     */
    private $endDate;

    /**
     * @var string
     *
     * @ORM\Column
     */
    private $designationType;

    /**
     * @var ElectionEntity
     *
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\VotingPlatform\ElectionEntity", cascade={"all"})
     */
    private $electionEntity;

    /**
     * @var CandidateGroup[]|ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="AppBundle\Entity\VotingPlatform\CandidateGroup",
     *     mappedBy="election",
     *     cascade={"all"},
     *     orphanRemoval=true
     * )
     */
    private $candidateGroups;

    public function __construct(
        string $title,
        string $designationType,
        \DateTime $startDate,
        \DateTime $endDate,
        UuidInterface $uuid = null
    ) {
        $this->title = $title;
        $this->designationType = $designationType;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->uuid = $uuid ?? Uuid::uuid4();

        $this->candidateGroups = new ArrayCollection();
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getStartDate(): \DateTime
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTime $startDate): void
    {
        $this->startDate = $startDate;
    }

    public function getEndDate(): \DateTime
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTime $endDate): void
    {
        $this->endDate = $endDate;
    }

    public function getDesignationType(): string
    {
        return $this->designationType;
    }

    public function getElectionEntity(): ElectionEntity
    {
        return $this->electionEntity;
    }

    public function setElectionEntity(ElectionEntity $electionEntity): void
    {
        $this->electionEntity = $electionEntity;
    }

    public function addCandidateGroup(CandidateGroup $candidateGroup): void
    {
        if (!$this->candidateGroups->contains($candidateGroup)) {
            $candidateGroup->setElection($this);
            $this->candidateGroups->add($candidateGroup);
        }
    }

    /**
     * @return CandidateGroup[]
     */
    public function getCandidateGroups(): array
    {
        return $this->candidateGroups->toArray();
    }
}
