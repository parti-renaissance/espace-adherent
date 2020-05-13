<?php

namespace App\Entity\Election;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use App\Entity\ElectionRound;
use App\Entity\VotePlace;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Election\VotePlaceResultRepository")
 *
 * @Algolia\Index(autoIndex=false)
 */
class VotePlaceResult extends BaseWithListCollectionResult
{
    /**
     * @var VotePlace
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\VotePlace", inversedBy="voteResults")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $votePlace;

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
