<?php

namespace AppBundle\Controller\Api;

use AppBundle\Entity\IdeasWorkshop\ThreadComment;
use Doctrine\Common\Persistence\ObjectManager;

class ThreadCommentController
{
    public function approveAction(ThreadComment $threadComment, ObjectManager $manager): ThreadComment
    {
        $threadComment->approve();
        $manager->flush();

        return $threadComment;
    }

    public function reportAction(ThreadComment $threadComment, ObjectManager $manager): ThreadComment
    {
        $threadComment->report();
        $manager->flush();

        return $threadComment;
    }
}
