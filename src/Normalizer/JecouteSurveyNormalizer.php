<?php

namespace App\Normalizer;

use App\Entity\Jecoute\Survey;
use App\Entity\Jecoute\SurveyQuestion;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class JecouteSurveyNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    protected const ALREADY_CALLED = 'JECOUTE_SURVEY_NORMALIZER_ALREADY_CALLED';

    /**
     * @param Survey $object
     *
     * @return array
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $context[static::ALREADY_CALLED] = true;

        $data = $this->normalizer->normalize($object, $format, $context);

        $data['questions'] = array_map(function (SurveyQuestion $surveyQuestion) use ($format, $context) {
            $question = $surveyQuestion->getQuestion();
            $choices = $this->normalizer->normalize($question->getChoicesOrdered(), $format, $context);
            $choices = array_values($choices);

            return [
                'id' => $surveyQuestion->getId(),
                'type' => $question->getType(),
                'content' => $question->getContent(),
                'choices' => $choices,
            ];
        }, $object->getQuestions()->toArray());

        return $data;
    }

    public function supportsNormalization($data, $format = null, array $context = [])
    {
        return !isset($context[static::ALREADY_CALLED])
            && $data instanceof Survey
            && 0 !== \count(array_intersect(['pap_campaign_survey_read', 'survey_list', 'survey_read_dc'], $context['groups'] ?? []))
        ;
    }
}
