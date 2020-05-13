<?php

namespace App\EntityListener;

use App\Entity\IdeasWorkshop\Vote;

class VoteListener
{
    public function prePersist(Vote $vote): void
    {
        $vote->getIdea()->incrementVotesCount();
    }

    public function preRemove(Vote $vote): void
    {
        $vote->getIdea()->decrementVotesCount();
    }
}
