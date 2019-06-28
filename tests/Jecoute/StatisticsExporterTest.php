<?php

namespace Tests\AppBundle\Jecoute;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Jecoute\LocalSurvey;
use AppBundle\Entity\Jecoute\Question;
use AppBundle\Entity\Jecoute\SurveyQuestion;
use AppBundle\Jecoute\StatisticsExporter;
use AppBundle\Jecoute\StatisticsProvider;
use AppBundle\Jecoute\SurveyQuestionTypeEnum;
use AppBundle\Repository\Jecoute\DataAnswerRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StatisticsExporterTest extends TestCase
{
    /** @var MockObject|StatisticsProvider */
    private $statisticsProvider;
    /** @var MockObject|DataAnswerRepository */
    private $dataAnswerRepository;
    /** @var MockObject|StatisticsExporter */
    private $statisticsExporter;

    protected function setUp(): void
    {
        $this->statisticsProvider = $this->getMockBuilder(StatisticsProvider::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $this->dataAnswerRepository = $this->getMockBuilder(DataAnswerRepository::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $this->statisticsExporter = new StatisticsExporter($this->statisticsProvider, $this->dataAnswerRepository);
    }

    public function testExport(): void
    {
        /** @var Adherent $author */
        $author = $this->createMock(Adherent::class);
        $survey = new LocalSurvey($author, 'Questionnaire TestU');

        $question = new Question('question test u', SurveyQuestionTypeEnum::MULTIPLE_CHOICE_TYPE);
        $surveyQuestion = new SurveyQuestion($survey, $question);
        $survey->addQuestion($surveyQuestion);

        $findDataByQuestionReturn = [
            'survey' => [
                'name' => 'Questionnaire numéro 1',
            ],
            'questions' => [
                0 => [
                    'content' => 'Ceci est-il un champ libre ?',
                    'type' => 'simple_field',
                    'stats' => [
                        'totalAnswered' => 3,
                    ],
                    'surveyQuestion' => $surveyQuestion,
                ],
                1 => [
                    'content' => 'Est-ce une question à choix multiple ?',
                    'type' => 'multiple_choice',
                    'stats' => [
                        'answers' => [
                            0 => [
                                'value' => 'Réponse A',
                                'percent' => '66.67',
                                'answered' => '2',
                            ],
                            1 => [
                                'value' => 'Réponse B',
                                'percent' => '33.33',
                                'answered' => '1',
                            ],
                        ],
                    ],
                    'surveyQuestion' => $surveyQuestion,
                ],
                2 => [
                    'uuid' => '',
                    'content' => 'Est-ce une question à choix unique ?',
                    'type' => 'unique_choice',
                    'stats' => [
                        'answers' => [
                            0 => [
                                'value' => 'Réponse A',
                                'percent' => '50.00',
                                'answered' => '1',
                            ],
                            1 => [
                                'value' => 'Réponse B',
                                'percent' => '50.00',
                                'answered' => '1',
                            ],
                        ],
                    ],
                    'surveyQuestion' => $surveyQuestion,
                ],
                3 => [
                    'uuid' => '',
                    'content' => 'Ceci est-il un champ libre d\'une question suggérée ?',
                    'type' => 'simple_field',
                    'stats' => [],
                    'surveyQuestion' => $surveyQuestion,
                ],
            ],
        ];

        $this->statisticsProvider->expects($this->once())->method('getStatsBySurvey')->willReturn($findDataByQuestionReturn);
        $this->dataAnswerRepository
            ->expects($this->exactly(2))
            ->method('findAllBySurveyQuestion')
            ->willReturn([
                ['textField' => 'Test Unit', 'postedAt' => new \DateTime('now')],
                ['textField' => 'Test Unit 2', 'postedAt' => '2019-01-01 01:00:00'],
            ], [])
        ;

        $statFile = $this->statisticsExporter->export($survey);

        $this->assertArrayHasKey('filename', $statFile);
        $this->assertArrayHasKey('content', $statFile);
        $this->assertInternalType('string', $statFile['content']);

        $this->assertSame(1, substr_count($statFile['content'], 'Questionnaire TestU '));
        $this->assertSame(1, substr_count($statFile['content'], 'Aucune donnée n\'est disponible pour le moment.'.\PHP_EOL));
        $this->assertSame(1, substr_count($statFile['content'], 'Est-ce une question à choix multiple ?'.\PHP_EOL));
        $this->assertSame(1, substr_count($statFile['content'], 'Réponse A;66.67%;2'.\PHP_EOL));
        $this->assertSame(1, substr_count($statFile['content'], 'Réponse A;50.00%;1'.\PHP_EOL));
        $this->assertSame(1, substr_count($statFile['content'], 'Test Unit 2;2019-01-01 01:00:00'.\PHP_EOL));
    }
}
