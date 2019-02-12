<?php

namespace AppBundle\EntityListener;

use AppBundle\Entity\IdeasWorkshop\Thread;

class IdeaThreadListener
{
    public function prePersist(Thread $thread): void
    {
        if ($thread->isEnabled()) {
            $thread->getIdea()->incrementCommentsCount();
        }
    }

    public function preRemove(Thread $thread): void
    {
        if ($thread->isEnabled()) {
            $thread->getIdea()->decrementCommentsCount();
        }
    }
}
