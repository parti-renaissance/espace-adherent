<?php

namespace App\Normalizer;

use App\Entity\Jecoute\Choice;
use App\Entity\Jecoute\DataAnswer;
use App\Entity\Jecoute\DataSurvey;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class CampaignRepliesDataSurveyNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const DATA_SURVEY_ALREADY_CALLED = 'DATA_SURVEY_NORMALIZER_ALREADY_CALLED';

    /**
     * @param DataSurvey $object
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $context[self::DATA_SURVEY_ALREADY_CALLED] = true;

        $dataSurvey = $this->normalizer->normalize($object, $format, $context);

        $dataSurvey['answers'] = array_map(function (DataAnswer $dataAnswer) {
            $question = $dataAnswer->getSurveyQuestion()->getQuestion();

            return [
                'question' => $question->getContent(),
                'answer' => $question->isChoiceType()
                    ? $this->transformSelectedChoicesCollection($dataAnswer->getSelectedChoices())
                    : $dataAnswer->getTextField(),
            ];
        }, $object->getAnswers()->toArray());

        return $dataSurvey;
    }

    public function supportsNormalization($data, $format = null, array $context = [])
    {
        return
            empty($context[self::DATA_SURVEY_ALREADY_CALLED])
            && $data instanceof DataSurvey
            && \in_array('campaign_replies_list', $context['groups'] ?? [])
        ;
    }

    private function transformSelectedChoicesCollection(Collection $selectedChoices): array
    {
        $choiceValues = [];

        /** @var Choice $choice */
        foreach ($selectedChoices as $choice) {
            $choiceValues[] = $choice->getContent();
        }

        return $choiceValues;
    }
}
