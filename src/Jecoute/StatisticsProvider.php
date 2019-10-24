<?php

namespace AppBundle\Jecoute;

use AppBundle\Entity\Jecoute\Survey;
use AppBundle\Repository\Jecoute\QuestionRepository;

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
            'questions' => $this->aggregateData($this->questionRepository->calculateStatistics($survey)),
        ];

        return $data;
    }

    private function aggregateData(array $stats): array
    {
        $data = [];

        foreach ($stats as $item) {
            $uuid = (string) $item['uuid'];

            if (!isset($data[$uuid])) {
                $data[$uuid] = [];
            }

            $data[$uuid][] = $item;
        }

        foreach ($data as &$items) {
            array_walk(
                $items,
                static function (&$item, $key, $total) { $item['total'] = $total; },
                array_sum(
                    array_column(
                        $items,
                        SurveyQuestionTypeEnum::SIMPLE_FIELD === $items[0]['type'] ? 'total_simple_field' : 'total_by_choice'
                    )
                )
            );
        }

        return $data;
    }
}
