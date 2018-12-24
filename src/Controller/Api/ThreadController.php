<?php

namespace AppBundle\Controller\Api;

use AppBundle\Entity\IdeasWorkshop\Thread;
use Doctrine\Common\Persistence\ObjectManager;

class ThreadController
{
    public function approveAction(Thread $thread, ObjectManager $manager): Thread
    {
        $thread->approve();
        $manager->flush();

        return $thread;
    }

    public function reportAction(Thread $thread, ObjectManager $manager): Thread
    {
        $thread->report();
        $manager->flush();

        return $thread;
    }
}
