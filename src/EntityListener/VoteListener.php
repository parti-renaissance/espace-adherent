<?php

namespace AppBundle\EntityListener;

use AppBundle\Entity\IdeasWorkshop\Vote;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;

class VoteListener
{
    public function prePersist(Vote $vote, LifecycleEventArgs $args)
    {
        $vote->getIdea()->incrementVotesCount();
    }

    public function preRemove(Vote $vote, LifecycleEventArgs $args)
    {
        $vote->getIdea()->decrementVotesCount();
    }
}
