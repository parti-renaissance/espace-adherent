<?php

namespace AppBundle\Controller\Api;

use AppBundle\Entity\IdeasWorkshop\ThreadComment;
use Symfony\Component\HttpFoundation\Request;

class ThreadCommentController
{
    public function approveAction(Request $request): ThreadComment
    {
        /** @var ThreadComment $threadComment */
        $threadComment = $request->attributes->get('data');

        $threadComment->approve();

        return $threadComment;
    }

    public function reportAction(Request $request): ThreadComment
    {
        /** @var ThreadComment $threadComment */
        $threadComment = $request->attributes->get('data');

        $threadComment->report();

        return $threadComment;
    }
}
