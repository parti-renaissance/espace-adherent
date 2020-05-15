<?php

namespace App\VotingPlatform\Collection;

use App\Entity\VotingPlatform\CandidateGroup;
use Doctrine\Common\Collections\ArrayCollection;

class CandidateGroupsCollection extends ArrayCollection
{
    /**
     * @return CandidateGroup[]
     */
    public function getWomanCandidateGroups(): array
    {
        return $this->filter(function (CandidateGroup $group) {
            return current($group->getCandidates())->isWoman();
        })->toArray();
    }

    /**
     * @return CandidateGroup[]
     */
    public function getManCandidateGroups(): array
    {
        return $this->filter(function (CandidateGroup $group) {
            return current($group->getCandidates())->isMan();
        })->toArray();
    }
}
