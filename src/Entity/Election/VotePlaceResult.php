<?php

namespace App\Entity\Election;

use App\Entity\ElectionRound;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Election\VotePlaceResultRepository")
 */
class VotePlaceResult extends BaseWithListCollectionResult
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Election\VotePlace")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private VotePlace $votePlace;

    public function __construct(VotePlace $votePlace, ElectionRound $electionRound)
    {
        parent::__construct($electionRound);

        $this->votePlace = $votePlace;
    }

    public function getVotePlace(): VotePlace
    {
        return $this->votePlace;
    }

    public function setVotePlace(VotePlace $votePlace): void
    {
        $this->votePlace = $votePlace;
    }
}
