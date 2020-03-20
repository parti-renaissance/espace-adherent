<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CommitteeElectionRepository")
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

    public function __construct(Committee $committee)
    {
        $this->committee = $committee;
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
