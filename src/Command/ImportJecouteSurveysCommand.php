<?php

namespace App\Command;

use App\Entity\Adherent;
use App\Entity\Jecoute\Choice;
use App\Entity\Jecoute\LocalSurvey;
use App\Entity\Jecoute\Question;
use App\Entity\Jecoute\SuggestedQuestion;
use App\Entity\Jecoute\SurveyQuestion;
use App\Jecoute\SurveyQuestionTypeEnum;
use App\Repository\AdherentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ImportJecouteSurveysCommand extends Command
{
    protected static $defaultName = 'app:jecoute-surveys:import';

    private const QUESTIONS = [
        [
            'content' => 'Quelle est la raison de votre présence à ... ?',
            'type' => SurveyQuestionTypeEnum::UNIQUE_CHOICE_TYPE,
            'choices' => [
                'Je suis résident(e)',
                'Pour le travail',
                'Pour le tourisme',
                'Autre',
            ],
        ],
        [
            'content' => 'Sur une échelle de 1 à 5, comment évaluez-vous l’intérêt que vous porterez aux élections municipales de 2020 (1 étant « aucun intérêt », 5 étant « grand intérêt »).',
            'type' => SurveyQuestionTypeEnum::UNIQUE_CHOICE_TYPE,
            'choices' => [
                '1 - Aucun intérêt',
                '2 - Faible intérêt',
                '3 - Intérêt moyen',
                '4 - Intéressé(e)',
                '5 - Grand intérêt',
            ],
            'suggested_question' => true,
        ],
        [
            'content' => 'Êtes-vous inscrit(e) sur les listes électorales ?',
            'type' => SurveyQuestionTypeEnum::UNIQUE_CHOICE_TYPE,
            'choices' => [
                'Oui et je connais mon bureau de vote',
                'Oui, mais je ne connais pas mon bureau de vote',
                'Non, mais je pense m\'inscrire pour les élections municipales de 2020',
                'Non et je ne pense pas m\'inscrire pour les élections municipales de 2020',
            ],
        ],
        [
            'content' => 'Quelle image avez-vous de ... ?',
            'type' => SurveyQuestionTypeEnum::SIMPLE_FIELD,
        ],

        [
            'content' => 'Pour vous quelles sont les 3 choses à améliorer en priorité dans cette localité ? (précisez pour chaque point)',
            'type' => SurveyQuestionTypeEnum::SIMPLE_FIELD,
            'suggested_question' => true,
        ],
        [
            'content' => 'A l’inverse, pour vous quelles sont les 3 choses qui fonctionnent le mieux ? (précisez pour chaque point)',
            'type' => SurveyQuestionTypeEnum::SIMPLE_FIELD,
            'suggested_question' => true,
        ],
        [
            'content' => 'Et si vous étiez maire, quelle serait votre première mesure ?',
            'type' => SurveyQuestionTypeEnum::SIMPLE_FIELD,
            'suggested_question' => true,
        ],
        [
            'content' => 'Souhaiteriez-vous être directement associé(e) à la prise de décision dans votre ville ?',
            'type' => SurveyQuestionTypeEnum::UNIQUE_CHOICE_TYPE,
            'choices' => [
                'Oui',
                'Non',
            ],
        ],
        [
            'content' => 'Si oui, de quelle façon aimeriez-vous y être associé(e) ?',
            'type' => SurveyQuestionTypeEnum::SIMPLE_FIELD,
        ],
        [
            'content' => 'Si non, pour quelle raison ne souhaitez-vous pas y être associé(e) ?',
            'type' => SurveyQuestionTypeEnum::SIMPLE_FIELD,
        ],
        [
            'content' => 'Sur une échelle de 1 à 5, à combien estimez-vous l’association des citoyens de ... aux décisions prises au niveau municipal (1 étant « pas du tout » et 5 étant « totalement ») ?',
            'type' => SurveyQuestionTypeEnum::UNIQUE_CHOICE_TYPE,
            'choices' => [
                '1 - Pas du tout associé(e)',
                '2 - Rarement associé(e)',
                '3 - Régulièrement associé(e)',
                '4 - Souvent associé(e)',
                '5 - Totalement associé(e)',
            ],
        ],
    ];

    private $em;
    private $adherentsRepository;

    /**
     * @var SymfonyStyle
     */
    private $io;

    public function __construct(EntityManagerInterface $em, AdherentRepository $adherentsRepository)
    {
        $this->em = $em;
        $this->adherentsRepository = $adherentsRepository;

        parent::__construct();
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io->text('Start importing suggested questions');

        $this->importSuggestedQuestions();

        $this->io->text('Start importing surveys');

        foreach ($this->adherentsRepository->findReferents() as $referent) {
            $this->importSurveyFor($referent);
        }

        $this->em->flush();

        $this->io->success('Done');
    }

    private function importSuggestedQuestions(): void
    {
        foreach (self::QUESTIONS as $questionDatas) {
            if (isset($questionDatas['suggested_question']) && true === $questionDatas['suggested_question']) {
                $suggestedQuestion = new SuggestedQuestion($questionDatas['content'], $questionDatas['type'], true);

                if (isset($questionDatas['choices'])) {
                    foreach ($questionDatas['choices'] as $choice) {
                        $suggestedQuestion->addChoice(new Choice($choice));
                    }
                }

                $this->em->persist($suggestedQuestion);
            }
        }
    }

    private function importSurveyFor(Adherent $referent): void
    {
        $survey = new LocalSurvey($referent, "Formulaire prêt à l'emploi", null, false);

        foreach (self::QUESTIONS as $questionDatas) {
            $question = new Question($questionDatas['content'], $questionDatas['type']);

            if (isset($questionDatas['choices'])) {
                foreach ($questionDatas['choices'] as $choice) {
                    $question->addChoice(new Choice($choice));
                }
            }

            $surveyQuestion = new SurveyQuestion($survey, $question);

            if (isset($questionDatas['suggested_question']) && true === $questionDatas['suggested_question']) {
                $surveyQuestion->setFromSuggestedQuestion(true);
            }

            $survey->addQuestion($surveyQuestion);
        }

        $this->em->persist($survey);
    }
}
