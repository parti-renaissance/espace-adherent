<?php

namespace AppBundle\Controller\Api\IdeasWorkshop;

use AppBundle\Entity\IdeasWorkshop\Thread;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ApproveThreadController
{
    public function __invoke(Request $request): Thread
    {
        /** @var Thread $object */
        $object = $request->attributes->get('data');

        if ($object->isApproved()) {
            throw new BadRequestHttpException('The thread is already approved');
        }

        $object->setApproved(true);

        return $object;
    }
}
