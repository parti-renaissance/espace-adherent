<?php

namespace AppBundle\Controller\Api;

use AppBundle\Entity\IdeasWorkshop\Idea;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class IdeaPublishController
{
    public function __invoke(Request $request): Idea
    {
        /** @var Idea $idea */
        $idea = $request->attributes->get('data');

        if (!$idea->isDraft()) {
            throw new BadRequestHttpException('You can publish only draft idea');
        }

        $idea->publish();

        return $idea;
    }
}
