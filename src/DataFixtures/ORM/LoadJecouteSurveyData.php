<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\DataFixtures\AutoIncrementResetter;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\Administrator;
use AppBundle\Entity\Jecoute\LocalSurvey;
use AppBundle\Entity\Jecoute\NationalSurvey;
use AppBundle\Entity\Jecoute\Question;
use AppBundle\Entity\Jecoute\SurveyQuestion;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadJecouteSurveyData extends Fixture
{
    public function load(ObjectManager $manager)
    {
        AutoIncrementResetter::resetAutoIncrement($manager, 'jecoute_survey');
        AutoIncrementResetter::resetAutoIncrement($manager, 'jecoute_survey_question');

        /** @var Adherent $referent1 */
        $referent1 = $this->getReference('adherent-8');

        /** @var Adherent $referent2 */
        $referent2 = $this->getReference('adherent-19');

        /** @var Administrator $administrator1 */
        $administrator1 = $this->getReference('administrator-1');

        /**
         * Local Surveys
         */
        $localSurvey1 = new LocalSurvey($referent1, 'Questionnaire numéro 1', 'Paris 1er', true);
        $localSurvey2 = new LocalSurvey($referent2, 'Un deuxième questionnaire', 'Paris 8ème', true);

        /** @var Question $question1 */
        $question1 = $this->getReference('question-1');

        /** @var Question $question2 */
        $question2 = $this->getReference('question-2');

        /** @var Question $question3 */
        $question3 = $this->getReference('question-3');

        /** @var Question $suggestedQuestion1 */
        $suggestedQuestion1 = $this->getReference(('suggested-question-1'));

        $surveyQuestion1 = new SurveyQuestion($localSurvey1, $question1);
        $surveyQuestion2 = new SurveyQuestion($localSurvey1, $question2);
        $surveyQuestion3 = new SurveyQuestion($localSurvey1, $question3);
        $surveyQuestion4 = new SurveyQuestion($localSurvey1, $suggestedQuestion1);
        $surveyQuestion4->setFromSuggestedQuestion(true);

        $localSurvey1->addQuestion($surveyQuestion1);
        $localSurvey1->addQuestion($surveyQuestion2);
        $localSurvey1->addQuestion($surveyQuestion3);
        $localSurvey1->addQuestion($surveyQuestion4);

        /** @var Question $question4 */
        $question4 = $this->getReference('question-4');

        $localSurvey2Question1 = new SurveyQuestion($localSurvey2, $question4);

        $localSurvey2->addQuestion($localSurvey2Question1);

        $manager->persist($localSurvey1);
        $manager->persist($localSurvey2);

        $this->addReference('survey-1', $localSurvey1);

        $this->addReference('survey-1-question-1', $surveyQuestion1);
        $this->addReference('survey-1-question-2', $surveyQuestion2);
        $this->addReference('survey-1-question-3', $surveyQuestion3);
        $this->addReference('survey-1-question-4', $surveyQuestion4);

        /**
         * National Surveys
         */
        $nationalSurvey1 = new NationalSurvey($administrator1, 'Questionnaire national numéro 1', true);

        /** @var Question $nationalQuestion1 */
        $nationalQuestion1 = $this->getReference('national-question-1');

        /** @var Question $nationalQuestion2 */
        $nationalQuestion2 = $this->getReference('national-question-2');

        $nationalSurveyQuestion1 = new SurveyQuestion($nationalSurvey1, $nationalQuestion1);
        $nationalSurveyQuestion2 = new SurveyQuestion($nationalSurvey1, $nationalQuestion2);

        $nationalSurvey1->addQuestion($nationalSurveyQuestion1);
        $nationalSurvey1->addQuestion($nationalSurveyQuestion2);

        $manager->persist($nationalSurvey1);

        $this->addReference('national-survey-1', $nationalSurvey1);

        $this->addReference('national-survey-1-question-1', $surveyQuestion1);
        $this->addReference('national-survey-1-question-2', $surveyQuestion2);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LoadAdherentData::class,
            LoadAdminData::class,
            LoadJecouteQuestionData::class,
            LoadJecouteSuggestedQuestionData::class,
        ];
    }
}
