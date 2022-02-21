<?php

namespace App\Normalizer;

use App\Entity\Jecoute\Choice;
use App\Entity\Jecoute\Question;
use App\Entity\Jecoute\Survey;
use App\Entity\Jecoute\SurveyQuestion;
use App\Jecoute\SurveyQuestionTypeEnum;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class UpdateSurveyDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    private const ALREADY_CALLED = 'JE_MENGAGE_WEB_SURVEY_UPDATE_DENORMALIZER_ALREADY_CALLED';

    public function denormalize($data, $type, $format = null, array $context = [])
    {
        $context[self::ALREADY_CALLED] = true;

        $questionData = $data['questions'] ?? null;
        unset($data['questions']);

        /** @var Survey $survey */
        $survey = $this->denormalizer->denormalize($data, $type, $format, $context);

        if (\is_array($questionData)) {
            foreach ($survey->getQuestions()->toArray() as $surveyQuestion) {
                if (!\in_array($surveyQuestion->getId(), $this->payloadIds($questionData), true)) {
                    $survey->removeQuestion($surveyQuestion);
                }
            }

            foreach ($questionData as $key => $dataQuestion) {
                $surveyQuestion = $this->handleChanges($survey, $dataQuestion, $format, $context);
                $surveyQuestion->setPosition($key);

                if (!$survey->getQuestions()->contains($surveyQuestion)) {
                    $survey->addQuestion($surveyQuestion);
                }
            }
        }

        return $survey;
    }

    public function supportsDenormalization($data, $type, $format = null, array $context = [])
    {
        return !isset($context[self::ALREADY_CALLED])
            && is_a($type, Survey::class, true)
            && 'put' === ($context['item_operation_name'] ?? null)
        ;
    }

    private function handleChanges(Survey $survey, array $question, $format = null, array $context = []): SurveyQuestion
    {
        if (isset($question['id'])) {
            $surveyQuestion = $this->denormalizer->denormalize($question['id'], SurveyQuestion::class, $format, $context);
        } else {
            $surveyQuestion = new SurveyQuestion();
        }

        if ($surveyQuestion->getQuestion()) {
            if ($surveyQuestion->getQuestion()->isChoiceType()
                && \in_array($question['question']['type'], SurveyQuestionTypeEnum::CHOICE_TYPES, true)
            ) {
                foreach ($surveyQuestion->getQuestion()->getChoices()->toArray() as $choice) {
                    if (!\in_array($choice->getId(), $this->payloadIds($question['question']['choices']), true)) {
                        $surveyQuestion->getQuestion()->removeChoice($choice);
                    }
                }
            }

            $this->applyChanges($surveyQuestion->getQuestion(), $question['question'], $format, $context);
        } else {
            $newQuestion = new Question();
            $this->applyChanges($newQuestion, $question['question'], $format, $context);
            $surveyQuestion->setQuestion($newQuestion);
            $surveyQuestion->setSurvey($survey);
        }

        return $surveyQuestion;
    }

    private function applyChanges(Question $question, array $data, $format = null, array $context = []): void
    {
        switch ($data['type']) {
            case SurveyQuestionTypeEnum::SIMPLE_FIELD:
                $question->setType($data['type']);
                $question->setContent($data['content']);
                break;
            case SurveyQuestionTypeEnum::MULTIPLE_CHOICE_TYPE:
            case SurveyQuestionTypeEnum::UNIQUE_CHOICE_TYPE:
                $question->setType($data['type']);
                $question->setContent($data['content']);
                $this->applyChoiceChanges($question, $data['choices'], $format, $context);
                break;
        }
    }

    private function applyChoiceChanges(Question $question, array $choices, $format = null, array $context = []): void
    {
        foreach ($choices as $key => $choiceData) {
            if (isset($choiceData['id'])) {
                $choice = $this->denormalizer->denormalize($choiceData['id'], Choice::class, $format, $context);
            } else {
                $choice = new Choice();
            }

            $choice->setContent($choiceData['content']);
            $choice->setPosition($key);

            if (!$question->getChoices()->contains($choice)) {
                $question->addChoice($choice);
            }
        }
    }

    private function payloadIds(array $data): array
    {
        return array_map('intval', array_column($data, 'id'));
    }
}
