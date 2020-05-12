<?php

namespace AppBundle\Entity\VotingPlatform;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use AppBundle\Entity\EntityIdentityTrait;
use AppBundle\Entity\VotingPlatform\Designation\Designation;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

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
     * @var Designation
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\VotingPlatform\Designation\Designation")
     */
    private $designation;

    /**
     * @var ElectionEntity
     *
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\VotingPlatform\ElectionEntity", mappedBy="election", cascade={"all"})
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

    public function __construct(Designation $designation, UuidInterface $uuid = null)
    {
        $this->designation = $designation;
        $this->uuid = $uuid ?? Uuid::uuid4();

        $this->candidateGroups = new ArrayCollection();
    }

    public function getTitle(): string
    {
        return $this->designation->getTitle();
    }

    public function getStartDate(): \DateTime
    {
        return $this->designation->getVoteStartDate();
    }

    public function getEndDate(): \DateTime
    {
        return $this->designation->getVoteEndDate();
    }

    public function getDesignationType(): string
    {
        return $this->designation->getType();
    }

    public function getElectionEntity(): ElectionEntity
    {
        return $this->electionEntity;
    }

    public function setElectionEntity(ElectionEntity $electionEntity): void
    {
        $electionEntity->setElection($this);
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
