<?php

namespace AppBundle\Controller\Api;

use AppBundle\Entity\IdeasWorkshop\ThreadComment;
use Doctrine\Common\Persistence\ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class ThreadCommentController
{
    /**
     * @ParamConverter("threadComment", options={"mapping": {"id": "uuid"}})
     */
    public function approveAction(ThreadComment $threadComment, ObjectManager $manager): ThreadComment
    {
        $threadComment->approve();
        $manager->flush();

        return $threadComment;
    }

    /**
     * @ParamConverter("threadComment", options={"mapping": {"id": "uuid"}})
     */
    public function reportAction(ThreadComment $threadComment, ObjectManager $manager): ThreadComment
    {
        $threadComment->report();
        $manager->flush();

        return $threadComment;
    }
}
