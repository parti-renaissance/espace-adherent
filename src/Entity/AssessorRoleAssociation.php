<?php

namespace App\Entity;

use App\Entity\Election\VotePlace as ElectionVotePlace;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class AssessorRoleAssociation
{
    /**
     * @var int|null
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Election\VotePlace")
     */
    private ?ElectionVotePlace $votePlace;

    public function __construct(ElectionVotePlace $votePlace)
    {
        $this->votePlace = $votePlace;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVotePlace(): ElectionVotePlace
    {
        return $this->votePlace;
    }
}
