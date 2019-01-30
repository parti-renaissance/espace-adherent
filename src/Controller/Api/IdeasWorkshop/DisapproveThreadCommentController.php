<?php

namespace AppBundle\Controller\Api\IdeasWorkshop;

use AppBundle\Entity\IdeasWorkshop\ThreadComment;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class DisapproveThreadCommentController
{
    public function __invoke(Request $request): ThreadComment
    {
        /** @var ThreadComment $object */
        $object = $request->attributes->get('data');

        if (!$object->isApproved()) {
            throw new BadRequestHttpException('The comment is already disapproved');
        }

        $object->setApproved(false);

        return $object;
    }
}
