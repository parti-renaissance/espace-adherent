<?php

namespace App\Normalizer;

use App\Entity\Jecoute\LocalSurvey;
use App\Entity\Jecoute\NationalSurvey;
use App\Entity\Jecoute\Survey;
use App\Entity\Jecoute\SurveyQuestion;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class JecouteSurveyNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * @param Survey $object
     *
     * @return array
     */
    public function normalize($object, $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = $this->normalizer->normalize($object, $format, $context + [__CLASS__ => true]);

        if ($object instanceof LocalSurvey) {
            $data['zone'] = $this->normalizer->normalize($object->getZone(), $format, $context);
        }

        if (!\in_array('survey_list_dc', $context['groups'])) {
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
        }

        return $data;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            '*' => null,
            Survey::class => false,
            LocalSurvey::class => false,
            NationalSurvey::class => false,
        ];
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return !isset($context[__CLASS__])
            && $data instanceof Survey
            && 0 !== \count(array_intersect(['pap_campaign_survey_read', 'survey_list', 'survey_read_dc', 'survey_list_dc'], $context['groups'] ?? []));
    }
}
