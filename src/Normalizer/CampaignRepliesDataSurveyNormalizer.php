<?php

declare(strict_types=1);

namespace App\Normalizer;

use App\Entity\Jecoute\Choice;
use App\Entity\Jecoute\DataSurvey;
use App\Entity\Jecoute\Survey;
use App\Entity\Jecoute\SurveyQuestion;
use App\Repository\Jecoute\SurveyQuestionRepository;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class CampaignRepliesDataSurveyNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private array $questionsCache = [];

    public function __construct(private readonly SurveyQuestionRepository $surveyQuestionRepository)
    {
    }

    /**
     * @param DataSurvey $object
     */
    public function normalize($object, $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $dataSurvey = $this->normalizer->normalize($object, $format, $context + [__CLASS__ => true]);

        if (\in_array('survey_replies_list', $context['groups'] ?? [], true)) {
            if ($object->isOfPapCampaignHistory()) {
                $dataSurvey['type'] = 'PAP';
                $dataSurvey['interviewed'] = [
                    'first_name' => $object->getPapCampaignHistory()->getFirstName(),
                    'last_name' => $object->getPapCampaignHistory()->getLastName(),
                    'gender' => $object->getPapCampaignHistory()->getGender(),
                    'age_range' => $object->getPapCampaignHistory()->getAgeRange(),
                ];
                $dataSurvey['begin_at'] = $dataSurvey['pap_campaign_history']['begin_at'] ?? null;
                $dataSurvey['finish_at'] = $dataSurvey['pap_campaign_history']['finish_at'] ?? null;
            } elseif ($object->isOfPhoningCampaignHistory()) {
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

        $answers = [];

        /** @var SurveyQuestion $surveyQuestion */
        foreach ($this->getQuestions($object->getSurvey()) as $surveyQuestion) {
            $questionName = $surveyQuestion->getQuestion()->getContent();
            $type = $surveyQuestion->getQuestion()->getType();

            $dataAnswer = $surveyQuestion->getDataAnswersFor($surveyQuestion, $object);

            if (!$dataAnswer) {
                $answers[$surveyQuestion->getPosition().'.'.$surveyQuestion->getId()] = [
                    'question_id' => $surveyQuestion->getId(),
                    'question' => $questionName,
                    'type' => $type,
                    'answer' => null,
                ];

                continue;
            }

            if ($surveyQuestion->getQuestion()->isChoiceType()) {
                $answers[$surveyQuestion->getPosition().'.'.$surveyQuestion->getId()] = [
                    'question_id' => $surveyQuestion->getId(),
                    'question' => $questionName,
                    'type' => $type,
                    'answer' => $dataAnswer->getSelectedChoices()->map(static function (Choice $choice) {
                        return $choice->getContent();
                    })->toArray(),
                ];

                continue;
            }

            $answers[$surveyQuestion->getPosition().'.'.$surveyQuestion->getId()] = [
                'question_id' => $surveyQuestion->getId(),
                'question' => $questionName,
                'type' => $type,
                'answer' => $dataAnswer->getTextField(),
            ];
        }

        ksort($answers);
        $dataSurvey['answers'] = array_values($answers);

        return $dataSurvey;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            DataSurvey::class => false,
        ];
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return
            !isset($context[__CLASS__])
            && $data instanceof DataSurvey
            && array_intersect(['phoning_campaign_replies_list', 'pap_campaign_replies_list', 'survey_replies_list'], $context['groups'] ?? []);
    }

    private function getQuestions(?Survey $survey): array
    {
        if (!$survey) {
            return [];
        }

        return $this->questionsCache[$survey->getId()] ?? $this->questionsCache[$survey->getId()] = $this->surveyQuestionRepository->findForSurvey($survey);
    }
}
