<?php

namespace App\Controller\Api\IdeasWorkshop;

use App\Entity\IdeasWorkshop\Thread;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ApproveThreadController
{
    public function approve(Request $request): Thread
    {
        /** @var Thread $object */
        $object = $request->attributes->get('data');

        if ($object->isApproved()) {
            throw new BadRequestHttpException('The thread is already approved');
        }

        $object->setApproved(true);

        return $object;
    }

    public function disapprove(Request $request): Thread
    {
        /** @var Thread $object */
        $object = $request->attributes->get('data');

        if (!$object->isApproved()) {
            throw new BadRequestHttpException('The thread is already disapproved');
        }

        $object->setApproved(false);

        return $object;
    }
}
