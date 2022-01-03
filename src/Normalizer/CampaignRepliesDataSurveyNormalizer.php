<?php

namespace App\Normalizer;

use App\Entity\Jecoute\Choice;
use App\Entity\Jecoute\DataSurvey;
use App\Entity\Jecoute\SurveyQuestion;
use App\Repository\Jecoute\SurveyQuestionRepository;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class CampaignRepliesDataSurveyNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const DATA_SURVEY_ALREADY_CALLED = 'DATA_SURVEY_NORMALIZER_ALREADY_CALLED';

    private SurveyQuestionRepository $surveyQuestionRepository;

    public function __construct(SurveyQuestionRepository $surveyQuestionRepository)
    {
        $this->surveyQuestionRepository = $surveyQuestionRepository;
    }

    /**
     * @param DataSurvey $object
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $context[self::DATA_SURVEY_ALREADY_CALLED] = true;

        $dataSurvey = $this->normalizer->normalize($object, $format, $context);

        $questions = $this->surveyQuestionRepository->findForSurvey($object->getSurvey());

        $answers = [];

        /** @var SurveyQuestion $surveyQuestion */
        foreach ($questions as $surveyQuestion) {
            $questionName = $surveyQuestion->getQuestion()->getContent();
            $type = $surveyQuestion->getQuestion()->getType();

            $dataAnswer = $surveyQuestion->getDataAnswersFor($surveyQuestion, $object);

            if (!$dataAnswer) {
                $answers[$surveyQuestion->getPosition().'.'.$surveyQuestion->getId()] = [
                    'question' => $questionName,
                    'type' => $type,
                    'answer' => null,
                ];

                continue;
            }

            if ($surveyQuestion->getQuestion()->isChoiceType()) {
                $answers[$surveyQuestion->getPosition().'.'.$surveyQuestion->getId()] = [
                    'question' => $questionName,
                    'type' => $type,
                    'answer' => $dataAnswer->getSelectedChoices()->map(static function (Choice $choice) {
                        return $choice->getContent();
                    })->toArray(),
                ];

                continue;
            }

            $answers[$surveyQuestion->getPosition().'.'.$surveyQuestion->getId()] = [
                'question' => $questionName,
                'type' => $type,
                'answer' => $dataAnswer->getTextField(),
            ];
        }

        ksort($answers);
        $dataSurvey['answers'] = array_values($answers);

        return $dataSurvey;
    }

    public function supportsNormalization($data, $format = null, array $context = [])
    {
        return
            empty($context[self::DATA_SURVEY_ALREADY_CALLED])
            && $data instanceof DataSurvey
            && array_intersect(['phoning_campaign_replies_list', 'pap_campaign_replies_list'], $context['groups'] ?? [])
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
