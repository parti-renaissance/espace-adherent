<?php

namespace App\Normalizer;

use ApiPlatform\Core\Exception\ItemNotFoundException;
use App\Entity\Jecoute\SurveyQuestion;
use App\Repository\Jecoute\SurveyQuestionRepository;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class SurveyQuestionFromIdDenormalizer implements DenormalizerInterface
{
    private $repository;

    public function __construct(SurveyQuestionRepository $repository)
    {
        $this->repository = $repository;
    }

    public function denormalize($data, $type, $format = null, array $context = [])
    {
        /** @var SurveyQuestion $surveyQuestion */
        if ($surveyQuestion = $this->repository->find($data)) {
            return $surveyQuestion;
        }

        throw new ItemNotFoundException();
    }

    public function supportsDenormalization($data, $type, $format = null, array $context = [])
    {
        return SurveyQuestion::class === $type && \is_integer($data);
    }
}
