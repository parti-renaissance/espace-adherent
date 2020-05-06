<?php

namespace App\DataFixtures\ORM;

use App\Entity\Jecoute\Choice;
use App\Entity\Jecoute\DataAnswer;
use App\Entity\Jecoute\DataSurvey;
use App\Entity\Jecoute\Survey;
use App\Entity\Jecoute\SurveyQuestion;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadJecouteDataAnswerData extends Fixture
{
    public function load(ObjectManager $manager)
    {
        /** @var SurveyQuestion $survey1Question1 */
        $survey1Question1 = $this->getReference('survey-1-question-1');

        /** @var SurveyQuestion $survey1Question2 */
        $survey1Question2 = $this->getReference('survey-1-question-2');

        /** @var SurveyQuestion $survey1Question3 */
        $survey1Question3 = $this->getReference('survey-1-question-3');

        /** @var DataSurvey $dataSurvey1 */
        $dataSurvey1 = $this->getReference('data-survey-1');

        /** @var DataSurvey $dataSurvey2 */
        $dataSurvey2 = $this->getReference('data-survey-2');

        /** @var DataSurvey $dataSurvey3 */
        $dataSurvey3 = $this->getReference('data-survey-3');

        // Data Survey 1

        $dataSurvey1Answer1 = new DataAnswer();
        $dataSurvey1Answer1->setSurveyQuestion($survey1Question1);
        $dataSurvey1Answer1->setDataSurvey($dataSurvey1);
        $dataSurvey1Answer1->setTextField('Bonsoir, ceci est une réponse A');

        /** @var Choice $surveyQuestion2Choice1 */
        $surveyQuestion2Choice1 = $this->getReference('question-2-choice-1');

        $dataSurvey1Answer2 = new DataAnswer();
        $dataSurvey1Answer2->setSurveyQuestion($survey1Question2);
        $dataSurvey1Answer2->setDataSurvey($dataSurvey1);
        $dataSurvey1Answer2->addSelectedChoice($surveyQuestion2Choice1);

        /** @var Choice $surveyQuestion3Choice1 */
        $surveyQuestion3Choice1 = $this->getReference('question-3-choice-1');

        $dataSurvey1Answer3 = new DataAnswer();
        $dataSurvey1Answer3->setSurveyQuestion($survey1Question3);
        $dataSurvey1Answer3->setDataSurvey($dataSurvey1);
        $dataSurvey1Answer3->addSelectedChoice($surveyQuestion3Choice1);

        $manager->persist($dataSurvey1Answer1);
        $manager->persist($dataSurvey1Answer2);
        $manager->persist($dataSurvey1Answer3);

        // Data Survey 2

        $dataSurvey2Answer1 = new DataAnswer();
        $dataSurvey2Answer1->setSurveyQuestion($survey1Question1);
        $dataSurvey2Answer1->setDataSurvey($dataSurvey2);
        $dataSurvey2Answer1->setTextField('Bonsoir, ceci est une réponse B');

        /** @var Choice $surveyQuestion2Choice1 */
        $surveyQuestion2Choice1 = $this->getReference('question-2-choice-1');

        $dataSurvey2Answer2 = new DataAnswer();
        $dataSurvey2Answer2->setSurveyQuestion($survey1Question2);
        $dataSurvey2Answer2->setDataSurvey($dataSurvey2);
        $dataSurvey2Answer2->addSelectedChoice($surveyQuestion2Choice1);

        /** @var Choice $surveyQuestion3Choice2 */
        $surveyQuestion3Choice2 = $this->getReference('question-3-choice-2');

        $dataSurvey2Answer3 = new DataAnswer();
        $dataSurvey2Answer3->setSurveyQuestion($survey1Question3);
        $dataSurvey2Answer3->setDataSurvey($dataSurvey2);
        $dataSurvey2Answer3->addSelectedChoice($surveyQuestion3Choice2);

        $manager->persist($dataSurvey1Answer1);
        $manager->persist($dataSurvey1Answer2);
        $manager->persist($dataSurvey1Answer3);

        $manager->persist($dataSurvey2Answer1);
        $manager->persist($dataSurvey2Answer2);
        $manager->persist($dataSurvey2Answer3);

        // Data Survey 3

        $dataSurvey3Answer1 = new DataAnswer();
        $dataSurvey3Answer1->setSurveyQuestion($survey1Question1);
        $dataSurvey3Answer1->setDataSurvey($dataSurvey3);
        $dataSurvey3Answer1->setTextField('Bonsoir, ceci est une réponse C');

        /** @var Choice $surveyQuestion2Choice2 */
        $surveyQuestion2Choice2 = $this->getReference('question-2-choice-2');

        $dataSurvey3Answer2 = new DataAnswer();
        $dataSurvey3Answer2->setSurveyQuestion($survey1Question2);
        $dataSurvey3Answer2->setDataSurvey($dataSurvey3);
        $dataSurvey3Answer2->addSelectedChoice($surveyQuestion2Choice2);

        $manager->persist($dataSurvey3Answer1);
        $manager->persist($dataSurvey3Answer2);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LoadJecouteSurveyData::class,
            LoadAdherentData::class,
            LoadJecouteQuestionData::class,
            LoadJecouteSuggestedQuestionData::class,
        ];
    }
}
