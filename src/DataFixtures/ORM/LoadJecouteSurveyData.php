<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\DataFixtures\AutoIncrementResetter;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\Jecoute\Question;
use AppBundle\Entity\Jecoute\Survey;
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
        $survey1 = new Survey($referent1, 'Questionnaire numéro 1', true);
        $survey2 = new Survey($referent1, 'Un deuxième questionnaire', true);

        /** @var Question $question1 */
        $question1 = $this->getReference('question-1');

        /** @var Question $question2 */
        $question2 = $this->getReference('question-2');

        /** @var Question $question3 */
        $question3 = $this->getReference('question-3');

        $surveyQuestion1 = new SurveyQuestion($survey1, $question1);
        $surveyQuestion2 = new SurveyQuestion($survey1, $question2);
        $surveyQuestion3 = new SurveyQuestion($survey1, $question3);

        $survey1->addQuestion($surveyQuestion1);
        $survey1->addQuestion($surveyQuestion2);
        $survey1->addQuestion($surveyQuestion3);

        $survey2Question1 = new SurveyQuestion($survey2, $question1);

        $survey2->addQuestion($survey2Question1);

        $manager->persist($survey1);
        $manager->persist($survey2);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LoadAdherentData::class,
            LoadJecouteQuestionData::class,
        ];
    }
}
