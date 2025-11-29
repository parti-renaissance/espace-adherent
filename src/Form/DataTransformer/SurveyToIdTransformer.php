<?php

declare(strict_types=1);

namespace App\Form\DataTransformer;

use App\Entity\Jecoute\Survey;
use App\Repository\Jecoute\SurveyRepository;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class SurveyToIdTransformer implements DataTransformerInterface
{
    private $surveyRepository;

    public function __construct(SurveyRepository $surveyRepository)
    {
        $this->surveyRepository = $surveyRepository;
    }

    public function transform($survey): mixed
    {
        return $survey instanceof Survey ? $survey->getId() : null;
    }

    public function reverseTransform($surveyId): mixed
    {
        $survey = $this->surveyRepository->findOneBy(['id' => $surveyId]);

        if (!$survey) {
            throw new TransformationFailedException(\sprintf('A Survey with id "%d" does not exist.', $surveyId));
        }

        return $survey;
    }
}
