<?php

namespace AppBundle\Controller\Api;

use AppBundle\Entity\IdeasWorkshop\Thread;
use Doctrine\Common\Persistence\ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class ThreadController
{
    /**
     * @ParamConverter("thread", options={"mapping": {"id": "uuid"}})
     */
    public function approveAction(Thread $thread, ObjectManager $manager): Thread
    {
        $thread->approve();
        $manager->flush();

        return $thread;
    }

    /**
     * @ParamConverter("thread", options={"mapping": {"id": "uuid"}})
     */
    public function reportAction(Thread $thread, ObjectManager $manager): Thread
    {
        $thread->report();
        $manager->flush();

        return $thread;
    }
}
