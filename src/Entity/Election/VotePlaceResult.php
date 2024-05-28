<?php

namespace App\Entity\Election;

use App\Entity\ElectionRound;
use App\Repository\Election\VotePlaceResultRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VotePlaceResultRepository::class)]
class VotePlaceResult extends BaseWithListCollectionResult
{
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: VotePlace::class)]
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
