<?php

namespace App\Normalizer;

use App\Entity\Jecoute\Survey;
use App\Repository\Jecoute\DataSurveyRepository;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class JeMengageWebSurveyNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public function __construct(private readonly DataSurveyRepository $dataSurveyRepository)
    {
    }

    /** @param Survey $object */
    public function normalize($object, $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $survey = $this->normalizer->normalize($object, $format, $context + [__CLASS__ => true]);
        $survey['nb_answers'] = $this->dataSurveyRepository->countSurveyDataAnswer($object);

        return $survey;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Survey::class => false,
        ];
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return !isset($context[__CLASS__])
            && $data instanceof Survey
            && \in_array('survey_list_dc', $context['groups'] ?? []);
    }
}
