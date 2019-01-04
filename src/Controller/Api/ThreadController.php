<?php

namespace AppBundle\Controller\Api;

use AppBundle\Entity\IdeasWorkshop\Thread;
use Symfony\Component\HttpFoundation\Request;

class ThreadController
{
    public function approveAction(Request $request): Thread
    {
        /** @var Thread $thread */
        $thread = $request->attributes->get('data');

        $thread->approve();

        return $thread;
    }
}
