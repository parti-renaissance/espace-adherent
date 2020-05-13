<?php

namespace App\EntityListener;

use App\Entity\IdeasWorkshop\Thread;

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
