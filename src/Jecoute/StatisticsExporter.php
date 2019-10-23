<?php

namespace AppBundle\Jecoute;

use AppBundle\Repository\Jecoute\DataAnswerRepository;
use Ramsey\Uuid\Uuid;

class StatisticsExporter
{
    private $dataAnswerRepository;
    private $contentParts;

    public function __construct(DataAnswerRepository $dataAnswerRepository)
    {
        $this->dataAnswerRepository = $dataAnswerRepository;
    }

    public function export(array $data): string
    {
        $this->buildHeader($data['survey']['name']);

        foreach ($data['questions'] as $items) {
            $this->contentParts[] = $items[0]['question_content'];

            switch ($items[0]['type']) {
                case SurveyQuestionTypeEnum::MULTIPLE_CHOICE_TYPE:
                case SurveyQuestionTypeEnum::UNIQUE_CHOICE_TYPE:
                    $this->buildRowsChoicesAnswers($items);
                    break;
                case SurveyQuestionTypeEnum::SIMPLE_FIELD:
                    $this->buildRowsSimpleField($items[0]);
                    break;
                default:
                    break;
            }

            $this->contentParts[] = '';
        }

        return implode(\PHP_EOL, $this->contentParts);
    }

    private function buildHeader(string $name): void
    {
        $this->contentParts[] = $name.' '.date('d/m/Y H:i');
        $this->contentParts[] = '';
        $this->contentParts[] = '';
    }

    private function buildRowsChoicesAnswers(array $stats): void
    {
        foreach ($stats as $answer) {
            $this->contentParts[] = implode(';', [
                $answer['choice_content'],
                round($answer['total_by_choice'] * 100 / $answer['total'], 2).'%',
                $answer['total_by_choice'],
            ]);
        }
    }

    private function buildRowsSimpleField(array $item): void
    {
        $responses = $this->dataAnswerRepository->findAllBySurveyQuestion(Uuid::fromString($item['uuid']));

        if (!empty($responses)) {
            foreach ($responses as $response) {
                $postAt = $response['postedAt'];

                if ($postAt instanceof \DateTimeInterface) {
                    $response['postedAt'] = $postAt->format('d/m/Y H:i');
                }

                $this->contentParts[] = implode(';', $response);
            }
        } else {
            $this->contentParts[] = 'Aucune donnée n\'est disponible pour le moment.';
        }
    }
}
