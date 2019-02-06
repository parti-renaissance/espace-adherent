<?php

namespace AppBundle\Jecoute;

use AppBundle\Entity\Jecoute\Survey;
use AppBundle\Entity\Jecoute\SurveyQuestion;
use AppBundle\Repository\Jecoute\DataAnswerRepository;

class StatisticsExporter
{
    private $dataProvider;
    private $dataAnswerRepository;
    private $content;

    public function __construct(StatisticsProvider $provider, DataAnswerRepository $dataAnswerRepository)
    {
        $this->dataProvider = $provider;
        $this->dataAnswerRepository = $dataAnswerRepository;
    }

    public function export(Survey $survey): array
    {
        return [
            'filename' => $this->getFilenameCsv($survey),
            'content' => $this->getContent($survey),
        ];
    }

    private function getFilenameCsv(Survey $survey): string
    {
        return str_replace(' ', '_', $survey->getName().' '.date('Y-m-d_H-i').'.csv');
    }

    private function getContent(Survey $survey): string
    {
        $data = $this->dataProvider->getStatsBySurvey($survey);
        $this->buildHeader($survey);

        foreach ($data['questions'] as $question) {
            $this->content .= $question['content'].\PHP_EOL;

            switch ($question['type']) {
                case SurveyQuestionTypeEnum::MULTIPLE_CHOICE_TYPE:
                case SurveyQuestionTypeEnum::UNIQUE_CHOICE_TYPE:
                    $this->buildRowsChoicesAnswers($question['stats']);
                    break;
                case SurveyQuestionTypeEnum::SIMPLE_FIELD:
                    $this->buildRowsSimpleField($question['surveyQuestion']);
                    break;
                default:
                    break;
            }

            $this->content .= \PHP_EOL;
        }

        return $this->content;
    }

    private function buildHeader(Survey $survey): void
    {
        $this->content = $survey->getName().' '.date('d/m/Y H:i').\PHP_EOL.\PHP_EOL;
    }

    private function buildRowsChoicesAnswers(array $stats): void
    {
        foreach ($stats['answers'] as $answer) {
            $answer['percent'] .= '%';
            $this->content .= implode(';', $answer).\PHP_EOL;
        }
    }

    private function buildRowsSimpleField(SurveyQuestion $surveyQuestion): void
    {
        $responses = $this->dataAnswerRepository->findAllBySurveyQuestion($surveyQuestion);

        if (!empty($responses)) {
            foreach ($responses as $response) {
                $postAt = $response['postedAt'];

                if ($postAt instanceof \DateTimeInterface) {
                    $response['postedAt'] = $postAt->format('d/m/Y H:i');
                }

                $this->content .= implode(';', $response).\PHP_EOL;
            }
        } else {
            $this->content .= 'Aucune donnée n\'est disponible pour le moment.'.\PHP_EOL;
        }
    }
}
