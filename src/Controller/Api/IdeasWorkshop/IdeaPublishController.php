<?php

namespace AppBundle\Controller\Api\IdeasWorkshop;

use AppBundle\Entity\IdeasWorkshop\Idea;
use AppBundle\Repository\IdeaWorkshopQuestionRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class IdeaPublishController
{
    public function __invoke(Request $request, IdeaWorkshopQuestionRepository $questionRepository): Idea
    {
        /** @var Idea $idea */
        $idea = $request->attributes->get('data');

        if (!$idea->isDraft()) {
            throw new BadRequestHttpException('You can publish only draft idea');
        }

        foreach ($idea->getAnswers() as $answer) {
            if (!\in_array($answer->getQuestion(), $questionRepository->getMandatoryQuestions())) {
                throw new BadRequestHttpException('You can publish only when all mandatory questions are answered');
            }
        }

        return $idea;
    }
}
