<?php

namespace App\DataFixtures\ORM;

use App\Entity\Adherent;
use App\Entity\Jecoute\DataSurvey;
use App\Entity\Jecoute\Survey;
use App\Jecoute\GenderEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadJecouteDataSurveyData extends Fixture
{
    public function load(ObjectManager $manager)
    {
        /** @var Survey $survey1 */
        $survey1 = $this->getReference('survey-1');

        /** @var Adherent $adherent7 */
        $adherent7 = $this->getReference('adherent-7');

        $dataSurvey1 = new DataSurvey($survey1, 'Juan', 'Nanardinho');
        $dataSurvey1->setAuthor($adherent7);
        $dataSurvey1->setGender(GenderEnum::MALE);

        $dataSurvey2 = new DataSurvey($survey1, 'Brigitte', 'Brioulini');
        $dataSurvey2->setAuthor($adherent7);
        $dataSurvey2->setGender(GenderEnum::FEMALE);

        $dataSurvey3 = new DataSurvey($survey1, 'Michel', 'Mimolette');
        $dataSurvey3->setAuthor($adherent7);
        $dataSurvey3->setGender(GenderEnum::MALE);

        $manager->persist($dataSurvey1);
        $manager->persist($dataSurvey2);
        $manager->persist($dataSurvey3);

        $this->addReference('data-survey-1', $dataSurvey1);
        $this->addReference('data-survey-2', $dataSurvey2);
        $this->addReference('data-survey-3', $dataSurvey3);

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
