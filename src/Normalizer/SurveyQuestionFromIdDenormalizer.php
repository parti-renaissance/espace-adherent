<?php

namespace App\Normalizer;

use ApiPlatform\Metadata\Exception\ItemNotFoundException;
use App\Entity\Jecoute\SurveyQuestion;
use App\Repository\Jecoute\SurveyQuestionRepository;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class SurveyQuestionFromIdDenormalizer implements DenormalizerInterface
{
    public function __construct(private readonly SurveyQuestionRepository $repository)
    {
    }

    public function denormalize($data, $type, $format = null, array $context = []): mixed
    {
        /** @var SurveyQuestion $surveyQuestion */
        if ($surveyQuestion = $this->repository->find($data)) {
            return $surveyQuestion;
        }

        throw new ItemNotFoundException();
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            '*' => null,
            SurveyQuestion::class => true,
        ];
    }

    public function supportsDenormalization($data, $type, $format = null, array $context = []): bool
    {
        return SurveyQuestion::class === $type && \is_int($data);
    }
}
