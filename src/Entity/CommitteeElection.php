<?php

namespace App\Entity;

use App\Entity\VotingPlatform\Designation\Designation;
use App\VotingPlatform\Designation\ElectionStaticDate;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CommitteeElectionRepository")
 */
class CommitteeElection
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var Committee
     *
     * @ORM\OneToOne(targetEntity="Committee", inversedBy="committeeElection")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $committee;

    /**
     * @var Designation
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\VotingPlatform\Designation\Designation")
     */
    private $designation;

    public function __construct(Designation $designation = null)
    {
        $this->designation = $designation;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getCommittee(): Committee
    {
        return $this->committee;
    }

    public function setCommittee(Committee $committee): void
    {
        $this->committee = $committee;
    }

    public function setDesignation(Designation $designation): void
    {
        $this->designation = $designation;
    }

    public function getCandidacyPeriodEndDate(): \DateTimeInterface
    {
        return ElectionStaticDate::getCandidacyPeriodEndDate();
    }

    public function getVoteStartDate(): \DateTimeInterface
    {
        return ElectionStaticDate::getVoteStartDate();
    }

    public function getVoteEndDate(): \DateTimeInterface
    {
        return ElectionStaticDate::getVoteEndDate();
    }
}
