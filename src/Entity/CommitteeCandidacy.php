<?php

namespace App\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use App\Entity\VotingPlatform\Designation\BaseCandidacy;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CommitteeCandidacyRepository")
 *
 * @Algolia\Index(autoIndex=false)
 */
class CommitteeCandidacy extends BaseCandidacy
{
    /**
     * @var CommitteeElection
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\CommitteeElection", inversedBy="candidacies")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $committeeElection;

    /**
     * @var CommitteeMembership
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\CommitteeMembership", inversedBy="committeeCandidacies")
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     */
    private $committeeMembership;

    public function __construct(CommitteeElection $election, string $gender = null, UuidInterface $uuid = null)
    {
        parent::__construct($gender, $uuid);

        $this->committeeElection = $election;
    }

    public function getCommitteeElection(): CommitteeElection
    {
        return $this->committeeElection;
    }

    public function setCommitteeElection(CommitteeElection $committeeElection): void
    {
        $this->committeeElection = $committeeElection;
    }

    public function getCommitteeMembership(): ?CommitteeMembership
    {
        return $this->committeeMembership;
    }

    public function setCommitteeMembership(CommitteeMembership $committeeMembership): void
    {
        $this->committeeMembership = $committeeMembership;
    }

    public function isOngoing(): bool
    {
        return $this->committeeElection->isOngoing();
    }
}
