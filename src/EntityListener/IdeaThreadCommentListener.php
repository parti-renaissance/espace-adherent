<?php

namespace AppBundle\EntityListener;

use AppBundle\Entity\IdeasWorkshop\ThreadComment;

class IdeaThreadCommentListener
{
    public function prePersist(ThreadComment $threadComment): void
    {
        if ($threadComment->isEnabled()) {
            $threadComment
                ->getThread()
                ->getIdea()
                ->incrementCommentsCount()
            ;
        }
    }

    public function preRemove(ThreadComment $threadComment): void
    {
        if ($threadComment->isEnabled()) {
            $threadComment
                ->getThread()
                ->getIdea()
                ->decrementCommentsCount()
            ;
        }
    }
}
