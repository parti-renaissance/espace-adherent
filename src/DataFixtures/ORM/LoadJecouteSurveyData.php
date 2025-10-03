<?php

namespace App\DataFixtures\ORM;

use App\Entity\Adherent;
use App\Entity\Administrator;
use App\Entity\Jecoute\LocalSurvey;
use App\Entity\Jecoute\NationalSurvey;
use App\Entity\Jecoute\Question;
use App\Entity\Jecoute\SuggestedQuestion;
use App\Entity\Jecoute\SurveyQuestion;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadJecouteSurveyData extends Fixture implements DependentFixtureInterface
{
    public const SURVEY_NATIONAL_1 = '13814039-1dd2-11b2-9bfd-78ea3dcdf0d9';
    public const SURVEY_NATIONAL_2 = '1f07832c-2a69-1e80-a33a-d5f9460e838f';
    public const SURVEY_NATIONAL_3 = '4c3594d4-fb6f-4e25-ac2e-7ef81694ec47';

    public const SURVEY_LOCAL_1 = '138140e9-1dd2-11b2-a08e-41ae5b09da7d';
    public const SURVEY_LOCAL_2 = 'dda4cd3a-f7ea-1bc6-9b2f-4bca1f9d02ea';
    public const SURVEY_LOCAL_3 = '478a2e65-7e86-1bb9-8078-8b70de061a8a';
    public const SURVEY_LOCAL_4 = '0de90b18-47f5-1606-af9d-74eb1fa4a30a';

    public function load(ObjectManager $manager): void
    {
        /** @var Adherent $referent1 */
        $referent1 = $this->getReference('adherent-8', Adherent::class);

        /** @var Adherent $referent2 */
        $referent2 = $this->getReference('adherent-19', Adherent::class);

        /** @var Adherent $headedRegionalCandidate */
        $headedRegionalCandidate = $this->getReference('adherent-3', Adherent::class);

        /** @var Administrator $administrator1 */
        $administrator1 = $this->getReference('administrator-1', Administrator::class);

        /** @var Administrator $administrator2 */
        $administrator2 = $this->getReference('administrator-2', Administrator::class);

        /**
         * Local Surveys
         */
        $localSurvey1 = new LocalSurvey(Uuid::fromString(self::SURVEY_LOCAL_1), 'Questionnaire numéro 1', true);
        $localSurvey1->setCreatedByAdherent($referent1);
        $localSurvey1->setCreatedAt(new \DateTime());

        $localSurvey2 = new LocalSurvey(Uuid::fromString(self::SURVEY_LOCAL_2), 'Un deuxième questionnaire', true);
        $localSurvey2->setCreatedByAdherent($referent2);

        $localSurvey3 = new LocalSurvey(Uuid::fromString(self::SURVEY_LOCAL_3), 'Un questionnaire de la Région', true);
        $localSurvey3->setCreatedByAdherent($headedRegionalCandidate);

        $localSurvey4 = new LocalSurvey(Uuid::fromString(self::SURVEY_LOCAL_4), 'Un questionnaire avec modification bloquée', true);
        $localSurvey4->setCreatedByAdherent($referent1);
        $localSurvey4->setBlockedChanges(true);

        /** @var Question $question1 */
        $question1 = $this->getReference('question-1', Question::class);

        /** @var Question $question2 */
        $question2 = $this->getReference('question-2', Question::class);

        /** @var Question $question3 */
        $question3 = $this->getReference('question-3', Question::class);

        /** @var SuggestedQuestion $suggestedQuestion1 */
        $suggestedQuestion1 = $this->getReference('suggested-question-1', SuggestedQuestion::class);

        $surveyQuestion1 = new SurveyQuestion($localSurvey1, $question1);
        $surveyQuestion2 = new SurveyQuestion($localSurvey1, $question2);
        $surveyQuestion3 = new SurveyQuestion($localSurvey1, $question3);
        $surveyQuestion4 = new SurveyQuestion(
            $localSurvey1,
            $question4 = new Question($suggestedQuestion1->getContent(), $suggestedQuestion1->getType())
        );
        $question4->setChoices($suggestedQuestion1->getChoices());
        $surveyQuestion4->setFromSuggestedQuestion($suggestedQuestion1->getId());

        $localSurvey1->addQuestion($surveyQuestion1);
        $localSurvey1->addQuestion($surveyQuestion2);
        $localSurvey1->addQuestion($surveyQuestion3);
        $localSurvey1->addQuestion($surveyQuestion4);

        $localSurvey1->setZone(LoadGeoZoneData::getZoneReference($manager, 'zone_department_77'));

        /** @var Question $question4 */
        $question4 = $this->getReference('question-4', Question::class);

        $localSurvey2Question1 = new SurveyQuestion($localSurvey2, $question4);

        $localSurvey2->addQuestion($localSurvey2Question1);

        $localSurvey2->setZone(LoadGeoZoneData::getZoneReference($manager, 'zone_department_59'));

        $localSurvey3->setZone(LoadGeoZoneData::getZoneReference($manager, 'zone_region_11'));
        $localSurvey4->setZone(LoadGeoZoneData::getZoneReference($manager, 'zone_department_92'));

        $manager->persist($localSurvey1);
        $manager->persist($localSurvey2);
        $manager->persist($localSurvey3);
        $manager->persist($localSurvey4);

        $this->addReference('survey-1', $localSurvey1);

        $this->addReference('survey-1-question-1', $surveyQuestion1);
        $this->addReference('survey-1-question-2', $surveyQuestion2);
        $this->addReference('survey-1-question-3', $surveyQuestion3);
        $this->addReference('survey-1-question-4', $surveyQuestion4);

        /**
         * National Surveys
         */

        // #1
        $nationalSurvey1 = new NationalSurvey(Uuid::fromString(self::SURVEY_NATIONAL_1), 'Questionnaire national numéro 1', true);
        $nationalSurvey1->setCreatedByAdministrator($administrator1);
        $nationalSurvey1->setCreatedAt(new \DateTime('-3 days'));

        /** @var Question $nationalQuestion1 */
        $nationalQuestion1 = $this->getReference('national-question-1', Question::class);

        /** @var Question $nationalQuestion2 */
        $nationalQuestion2 = $this->getReference('national-question-2', Question::class);

        $nationalSurveyQuestion1 = new SurveyQuestion($nationalSurvey1, $nationalQuestion1);
        $nationalSurveyQuestion2 = new SurveyQuestion($nationalSurvey1, $nationalQuestion2);

        $nationalSurvey1->addQuestion($nationalSurveyQuestion1);
        $nationalSurvey1->addQuestion($nationalSurveyQuestion2);

        $manager->persist($nationalSurvey1);

        $this->addReference('national-survey-1', $nationalSurvey1);

        $this->addReference('national-survey-1-question-1', $nationalSurveyQuestion1);
        $this->addReference('national-survey-1-question-2', $nationalSurveyQuestion2);

        // #2
        $nationalSurvey2 = new NationalSurvey(Uuid::fromString(self::SURVEY_NATIONAL_2), 'Le deuxième questionnaire national', true);
        $nationalSurvey2->setCreatedByAdministrator($administrator2);
        $nationalSurvey2->setCreatedAt(new \DateTime('-2 days'));

        /** @var Question $nationalQuestion3 */
        $nationalQuestion3 = $this->getReference('national-question-3', Question::class);

        $nationalSurveyQuestion3 = new SurveyQuestion($nationalSurvey2, $nationalQuestion3);

        $nationalSurvey2->addQuestion($nationalSurveyQuestion3);

        $manager->persist($nationalSurvey2);

        $this->addReference('national-survey-2', $nationalSurvey2);

        $this->addReference('national-survey-2-question-1', $nationalSurveyQuestion3);

        // #3
        $nationalSurvey3 = new NationalSurvey(Uuid::fromString(self::SURVEY_NATIONAL_3), 'Les enjeux des 10 prochaines années', true);
        $nationalSurvey3->setCreatedByAdministrator($administrator1);
        $nationalSurvey3->setCreatedAt(new \DateTime('-1 day'));

        /** @var Question $nationalQuestion4 */
        $nationalQuestion4 = $this->getReference('national-question-4', Question::class);

        /** @var Question $nationalQuestion5 */
        $nationalQuestion5 = $this->getReference('national-question-5', Question::class);

        $nationalSurveyQuestion4 = new SurveyQuestion($nationalSurvey3, $nationalQuestion4);
        $nationalSurveyQuestion5 = new SurveyQuestion($nationalSurvey3, $nationalQuestion5);

        $nationalSurvey3->addQuestion($nationalSurveyQuestion4);
        $nationalSurvey3->addQuestion($nationalSurveyQuestion5);

        $manager->persist($nationalSurvey3);

        $this->addReference('national-survey-3', $nationalSurvey3);

        $this->addReference('national-survey-3-question-4', $nationalSurveyQuestion4);
        $this->addReference('national-survey-3-question-5', $nationalSurveyQuestion5);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            LoadAdherentData::class,
            LoadAdminData::class,
            LoadJecouteQuestionData::class,
            LoadJecouteSuggestedQuestionData::class,
        ];
    }
}
