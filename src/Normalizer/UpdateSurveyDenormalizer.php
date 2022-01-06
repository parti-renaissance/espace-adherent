<?php

namespace App\Normalizer;

use App\Entity\Jecoute\Survey;
use App\Entity\Jecoute\SurveyQuestion;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class UpdateSurveyDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    private const ALREADY_CALLED = 'JE_MENGAGE_WEB_SURVEY_DENORMALIZER_ALREADY_CALLED';

    public function denormalize($data, $type, $format = null, array $context = [])
    {
        $context[self::ALREADY_CALLED] = true;

        $survey = $this->denormalizer->denormalize($data, $type, $format, $context);

//        if (isset($data['questions'])) {
//            foreach ($data['questions'] as $key => $dataQuestion) {
//                if (isset($dataQuestion['id'])) {
//                    $surveyQuestion = $this->denormalizer->denormalize($dataQuestion['id'], SurveyQuestion::class, $format, $context);
//                } else {
//                    //$surveyQuestion = new SurveyQuestion()
//                }
//            }
//        }

        return $survey;
    }

    public function supportsDenormalization($data, $type, $format = null, array $context = [])
    {
        return !isset($context[self::ALREADY_CALLED])
            && is_a($type, Survey::class, true)
            && \in_array('survey_update_dc', $context['groups'] ?? [])
            ;
    }
}
