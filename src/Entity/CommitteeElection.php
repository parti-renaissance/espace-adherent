<?php

namespace App\Entity;

use App\Entity\VotingPlatform\Designation\Designation;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CommitteeElectionRepository")
 */
class CommitteeElection
{
    use EntityDesignationTrait;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var Committee
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Committee", inversedBy="committeeElections")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $committee;

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
}
