<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CommitteeCandidacyRepository")
 */
class CommitteeCandidacy
{
    use TimestampableEntity;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var CommitteeElection
     *
     * @ORM\ManyToOne(targetEntity="CommitteeElection")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $committeeElection;

    public function __construct(CommitteeElection $election)
    {
        $this->committeeElection = $election;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCommitteeElection(): CommitteeElection
    {
        return $this->committeeElection;
    }

    public function setCommitteeElection(CommitteeElection $committeeElection): void
    {
        $this->committeeElection = $committeeElection;
    }
}
