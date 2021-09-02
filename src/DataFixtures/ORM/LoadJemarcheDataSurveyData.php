<?php

namespace App\DataFixtures\ORM;

use App\Entity\Adherent;
use App\Entity\Jecoute\DataSurvey;
use App\Entity\Jecoute\JemarcheDataSurvey;
use App\Entity\Jecoute\NationalSurvey;
use App\Entity\Jecoute\Survey;
use App\Jecoute\GenderEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadJemarcheDataSurveyData extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        /** @var Survey $survey1 */
        $survey1 = $this->getReference('survey-1');

        /** @var NationalSurvey $nationalSurvey1 */
        $nationalSurvey1 = $this->getReference('national-survey-1');

        /** @var NationalSurvey $nationalSurvey2 */
        $nationalSurvey2 = $this->getReference('national-survey-2');

        /** @var Adherent $adherent7 */
        $adherent7 = $this->getReference('adherent-7');

        $dataSurvey1 = $this->createDataSurvey($survey1, $adherent7, 'Juan', 'Nanardinho', GenderEnum::MALE);
        $dataSurvey2 = $this->createDataSurvey($survey1, $adherent7, 'Brigitte', 'Brioulini', GenderEnum::FEMALE);
        $dataSurvey3 = $this->createDataSurvey($survey1, $adherent7, 'Michel', 'Mimolette', GenderEnum::MALE);
        $dataSurvey4 = $this->createDataSurvey($nationalSurvey1, $adherent7, 'Roger', 'Camembert', GenderEnum::MALE);
        $dataSurvey5 = $this->createDataSurvey($nationalSurvey1, $adherent7, 'Sophie', 'Stiket', GenderEnum::FEMALE);
        $dataSurvey6 = $this->createDataSurvey($nationalSurvey2, $adherent7, 'Pierre', 'Feuilcizo', GenderEnum::MALE);

        $manager->persist($dataSurvey1);
        $manager->persist($dataSurvey2);
        $manager->persist($dataSurvey3);
        $manager->persist($dataSurvey4);
        $manager->persist($dataSurvey5);
        $manager->persist($dataSurvey6);

        $this->addReference('data-survey-1', $dataSurvey1->getDataSurvey());
        $this->addReference('data-survey-2', $dataSurvey2->getDataSurvey());
        $this->addReference('data-survey-3', $dataSurvey3->getDataSurvey());
        $this->addReference('data-national-survey-1', $dataSurvey4->getDataSurvey());
        $this->addReference('data-national-survey-2', $dataSurvey5->getDataSurvey());
        $this->addReference('data-national-survey-3', $dataSurvey6->getDataSurvey());

        $manager->flush();
    }

    public function createDataSurvey(
        Survey $survey,
        Adherent $author,
        string $firstName,
        string $lastName,
        string $gender
    ): JemarcheDataSurvey {
        $dataSurvey = new DataSurvey($survey);
        $dataSurvey->setAuthor($author);
        $jemarcheDataSurvey = new JemarcheDataSurvey();
        $jemarcheDataSurvey->setGender($gender);
        $jemarcheDataSurvey->setFirstName($firstName);
        $jemarcheDataSurvey->setLastName($lastName);
        $jemarcheDataSurvey->setDataSurvey($dataSurvey);

        return $jemarcheDataSurvey;
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
