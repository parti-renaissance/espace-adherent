<?php

namespace App\Controller\Api\IdeasWorkshop;

use App\Entity\IdeasWorkshop\Idea;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class IdeaController
{
    public function publish(Request $request): Idea
    {
        /** @var Idea $idea */
        $idea = $request->attributes->get('data');

        if (!$idea->isDraft()) {
            throw new BadRequestHttpException('You can publish only draft idea');
        }

        $idea->publish();

        return $idea;
    }

    public function extend(Request $request): Idea
    {
        /** @var Idea $idea */
        $idea = $request->attributes->get('data');

        if (!$idea->isExtendable()) {
            throw new BadRequestHttpException(sprintf('You can extend only PENDING or FINALIZED idea and do it %d times', Idea::EXTEND_COUNT_LIMIT));
        }

        $idea->extend();

        return $idea;
    }
}
