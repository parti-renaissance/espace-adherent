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

    private const SURVEY_ALREADY_CALLED = 'JE_MENGAGE_WEB_SURVEY_NORMALIZER_ALREADY_CALLED';

    private DataSurveyRepository $dataSurveyRepository;

    public function __construct(DataSurveyRepository $dataSurveyRepository)
    {
        $this->dataSurveyRepository = $dataSurveyRepository;
    }

    /** @param Survey $object */
    public function normalize($object, $format = null, array $context = [])
    {
        $context[self::SURVEY_ALREADY_CALLED] = true;

        $survey = $this->normalizer->normalize($object, $format, $context);
        $survey['nb_answers'] = $this->dataSurveyRepository->countSurveyDataAnswer($object);

        return $survey;
    }

    public function supportsNormalization($data, $format = null, array $context = [])
    {
        return !isset($context[self::SURVEY_ALREADY_CALLED])
            && $data instanceof Survey
            && \in_array('survey_list_dc', $context['groups'] ?? [])
        ;
    }
}
