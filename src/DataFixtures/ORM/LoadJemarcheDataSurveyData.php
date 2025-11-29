<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Entity\Adherent;
use App\Entity\Jecoute\DataSurvey;
use App\Entity\Jecoute\JemarcheDataSurvey;
use App\Entity\Jecoute\LocalSurvey;
use App\Entity\Jecoute\NationalSurvey;
use App\Entity\Jecoute\Survey;
use App\Jecoute\GenderEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadJemarcheDataSurveyData extends Fixture implements DependentFixtureInterface
{
    public const JEMARCHE_DATA_SURVEY_1_UUID = '5191f388-ccb0-4a93-b7f9-a15f107287fb';

    public function load(ObjectManager $manager): void
    {
        /** @var Survey $survey1 */
        $survey1 = $this->getReference('survey-1', LocalSurvey::class);

        /** @var NationalSurvey $nationalSurvey1 */
        $nationalSurvey1 = $this->getReference('national-survey-1', NationalSurvey::class);

        /** @var Adherent $adherent7 */
        $adherent7 = $this->getReference('adherent-7', Adherent::class);

        $dataSurvey1 = $this->createDataSurvey($adherent7, 'Juan', 'Nanardinho', GenderEnum::MALE, $survey1, null, null, 48.5182194, 2.624205);
        $dataSurvey2 = $this->createDataSurvey($adherent7, 'Brigitte', 'Brioulini', GenderEnum::FEMALE, $survey1);
        $dataSurvey3 = $this->createDataSurvey($adherent7, 'Michel', 'Mimolette', GenderEnum::MALE, $survey1);
        $dataSurvey4 = $this->createDataSurvey($adherent7, 'Roger', 'Camembert', GenderEnum::MALE, $nationalSurvey1);
        $dataSurvey5 = $this->createDataSurvey($adherent7, 'Sophie', 'Stiket', GenderEnum::FEMALE, $nationalSurvey1);
        $dataSurvey6 = $this->createDataSurvey($adherent7, 'Pierre', 'Feuilcizo', GenderEnum::MALE, $nationalSurvey1, null, null, 48.5182194, 2.624205);
        $dataSurvey7 = $this->createDataSurvey($adherent7, 'Maria', 'Mozzarella', GenderEnum::MALE, null, 'maria@mozzarella.com', self::JEMARCHE_DATA_SURVEY_1_UUID);

        $manager->persist($dataSurvey1);
        $manager->persist($dataSurvey2);
        $manager->persist($dataSurvey3);
        $manager->persist($dataSurvey4);
        $manager->persist($dataSurvey5);
        $manager->persist($dataSurvey6);
        $manager->persist($dataSurvey7);

        $this->addReference('data-survey-1', $dataSurvey1->getDataSurvey());
        $this->addReference('data-survey-2', $dataSurvey2->getDataSurvey());
        $this->addReference('data-survey-3', $dataSurvey3->getDataSurvey());
        $this->addReference('data-national-survey-1', $dataSurvey4->getDataSurvey());
        $this->addReference('data-national-survey-2', $dataSurvey5->getDataSurvey());
        $this->addReference('data-national-survey-3', $dataSurvey6->getDataSurvey());

        $manager->flush();
    }

    public function createDataSurvey(
        Adherent $author,
        string $firstName,
        string $lastName,
        string $gender,
        ?Survey $survey = null,
        ?string $emailAddress = null,
        ?string $uuid = null,
        ?float $latitude = null,
        ?float $longitude = null,
    ): JemarcheDataSurvey {
        $jemarcheDataSurvey = new JemarcheDataSurvey($uuid ? Uuid::fromString($uuid) : null);
        $jemarcheDataSurvey->setGender($gender);
        $jemarcheDataSurvey->setFirstName($firstName);
        $jemarcheDataSurvey->setLastName($lastName);
        $jemarcheDataSurvey->setEmailAddress($emailAddress);
        $jemarcheDataSurvey->setLatitude($latitude);
        $jemarcheDataSurvey->setLongitude($longitude);

        if ($survey) {
            $dataSurvey = new DataSurvey($survey);
            $dataSurvey->setAuthor($author);
            $dataSurvey->setAuthorPostalCode($author->getPostalCode());
            $jemarcheDataSurvey->setDataSurvey($dataSurvey);
        }

        return $jemarcheDataSurvey;
    }

    public function getDependencies(): array
    {
        return [
            LoadJecouteSurveyData::class,
            LoadAdherentData::class,
            LoadJecouteQuestionData::class,
            LoadJecouteSuggestedQuestionData::class,
        ];
    }
}
