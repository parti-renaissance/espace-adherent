<?php

namespace AppBundle\Jecoute;

use AppBundle\Entity\Jecoute\Survey;
use AppBundle\Entity\Jecoute\SurveyQuestion;
use AppBundle\Repository\Jecoute\QuestionRepository;
use Doctrine\Common\Collections\Collection;

class StatisticsProvider
{
    private $questionRepository;

    public function __construct(QuestionRepository $questionRepository)
    {
        $this->questionRepository = $questionRepository;
    }

    public function getStatsBySurvey(Survey $survey): array
    {
        $data = [
            'survey' => [
                'uuid' => $survey->getUuid(),
                'name' => $survey->getName(),
                'isNational' => $survey->isNational(),
            ],
            'questions' => $this->createQuestions($survey->getQuestions()),
        ];

        return $data;
    }

    private function createQuestions(Collection $questions): array
    {
        $data = [];

        /** @var SurveyQuestion $surveyQuestion */
        foreach ($questions as $surveyQuestion) {
            $question = $surveyQuestion->getQuestion();
            $dataByQuestion = $this->questionRepository->findDataByQuestion($question);
            $totalAnswered = $this->calculateTotal($dataByQuestion, $question->getType());

            $data[] = [
                'uuid' => $surveyQuestion->getUuid(),
                'content' => $question->getContent(),
                'type' => $question->getType(),
                'stats' => $this->createDataAnswers($dataByQuestion, $totalAnswered, $question->getType()),
                'surveyQuestion' => $surveyQuestion,
            ];
        }

        return $data;
    }

    private function createDataAnswers(array $dataBySurveyQuestion, int $totalAnswered, string $type): array
    {
        $data = [];

        if (SurveyQuestionTypeEnum::SIMPLE_FIELD !== $type) {
            foreach ($dataBySurveyQuestion as $result) {
                $data['answers'][] = [
                    'value' => $result['content'],
                    'percent' => $totalAnswered > 0
                        ? str_replace('.00', '', number_format(($result['choicesCount'] * 100) / $totalAnswered, 2))
                        : 0,
                    'answered' => $result['choicesCount'],
                ];
            }
        }

        $data['totalAnswered'] = $totalAnswered;

        return $data;
    }

    private function calculateTotal(array $data, string $type): int
    {
        return array_sum(array_column(
            $data,
            SurveyQuestionTypeEnum::SIMPLE_FIELD === $type ? 'textFieldsCount' : 'choicesCount'
        ));
    }
}
