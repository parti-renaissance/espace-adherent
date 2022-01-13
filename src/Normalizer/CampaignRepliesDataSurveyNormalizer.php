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

        if (\in_array('survey_replies_list', $context['groups'] ?? [], true)) {
            if ($object->isOfPhoningCampaignHistory()) {
                $dataSurvey['type'] = 'Phoning';
                $dataSurvey['interviewed'] = $object->getPhoningCampaignHistory()->getAdherent()
                    ? [
                        'first_name' => $object->getPhoningCampaignHistory()->getAdherent()->getFirstName(),
                        'last_name' => $object->getPhoningCampaignHistory()->getAdherent()->getFirstName(),
                        'gender' => $object->getPhoningCampaignHistory()->getAdherent()->getGender(),
                        'age_range' => null,
                    ]
                    : null;
                $dataSurvey['begin_at'] = $dataSurvey['phoning_campaign_history']['begin_at'] ?? null;
                $dataSurvey['finish_at'] = $dataSurvey['phoning_campaign_history']['finish_at'] ?? null;
            } elseif ($object->isOfPapCampaignHistory()) {
                $dataSurvey['type'] = 'PAP';
                $dataSurvey['interviewed'] = [
                    'first_name' => $object->getPapCampaignHistory()->getFirstName(),
                    'last_name' => $object->getPapCampaignHistory()->getLastName(),
                    'gender' => $object->getPapCampaignHistory()->getGender(),
                    'age_range' => $object->getPapCampaignHistory()->getAgeRange(),
                ];
                $dataSurvey['begin_at'] = $dataSurvey['pap_campaign_history']['begin_at'] ?? null;
                $dataSurvey['finish_at'] = $dataSurvey['pap_campaign_history']['finish_at'] ?? null;
            } elseif ($object->isOfJemarcheDataSurvey()) {
                $dataSurvey['type'] = 'Libre';
                $dataSurvey['interviewed'] = [
                    'first_name' => $object->getJemarcheDataSurvey()->getFirstName(),
                    'last_name' => $object->getJemarcheDataSurvey()->getLastName(),
                    'gender' => $object->getJemarcheDataSurvey()->getGender(),
                    'age_range' => $object->getJemarcheDataSurvey()->getAgeRange(),
                ];
                $dataSurvey['begin_at'] = null;
                $dataSurvey['finish_at'] = $this->normalizer->normalize($object->getPostedAt(), $format, $context);
            }
            unset($dataSurvey['pap_campaign_history'], $dataSurvey['phoning_campaign_history']);
        }
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
            && array_intersect(['phoning_campaign_replies_list', 'pap_campaign_replies_list', 'survey_replies_list'], $context['groups'] ?? [])
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
