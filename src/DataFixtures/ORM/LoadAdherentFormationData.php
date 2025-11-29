<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Entity\Adherent;
use App\Entity\AdherentFormation\Formation;
use App\Entity\AdherentFormation\FormationContentTypeEnum;
use App\Entity\Administrator;
use App\Entity\Geo\Zone;
use App\Formation\FormationHandler;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class LoadAdherentFormationData extends Fixture implements DependentFixtureInterface
{
    public const FORMATION_1_UUID = '906ec77b-f773-467a-8a07-342b3b1d9bac';
    public const FORMATION_2_UUID = 'a395f0d0-26ac-4cd8-bdf7-2918799aba7f';
    public const FORMATION_3_UUID = '55034c8d-b9b9-448b-b29c-64bc23be486c';
    public const FORMATION_4_UUID = 'ebdbafa2-c0b0-40ff-adbd-745f48f48c42';
    public const FORMATION_5_UUID = '366c1da2-f833-4172-883a-c10a41588766';
    public const FORMATION_6_UUID = '405ffc51-810e-4b2b-8b7c-4bea1384a164';

    private Generator $faker;

    public function __construct(private readonly FormationHandler $formationHandler)
    {
        $this->faker = Factory::create('fr_FR');
    }

    public function load(ObjectManager $manager): void
    {
        /** @var Administrator $administrator */
        $administrator = $this->getReference('administrator-renaissance', Administrator::class);

        $formation = $this->createNationalFormation(self::FORMATION_1_UUID, $administrator, 'Première formation nationale');
        $formation->setCategory('Catégorie 1');
        $formation->setPosition(1);
        $formation->setContentType(FormationContentTypeEnum::FILE);
        $this->createFile($formation);
        $manager->persist($formation);

        $formation = $this->createNationalFormation(self::FORMATION_2_UUID, $administrator, 'Formation sans description', false);
        $formation->setCategory('Catégorie 1');
        $formation->setPosition(2);
        $formation->setContentType(FormationContentTypeEnum::LINK);
        $formation->setLink('http://enmarche.code/');
        $manager->persist($formation);

        $formation = $this->createNationalFormation(self::FORMATION_3_UUID, $administrator, 'Formation non publiée', true, false);
        $formation->setCategory('Catégorie 2');
        $formation->setPosition(3);
        $formation->setContentType(FormationContentTypeEnum::FILE);
        $this->createFile($formation);
        $manager->persist($formation);

        $referent92 = $this->getReference('adherent-8', Adherent::class);
        $zoneDepartment92 = LoadGeoZoneData::getZoneReference($manager, 'zone_department_92');

        $formation = $this->createLocalFormation(self::FORMATION_4_UUID, $referent92, $zoneDepartment92, 'Première formation du 92');
        $formation->setPosition(1);
        $formation->setContentType(FormationContentTypeEnum::FILE);
        $this->createFile($formation);
        $manager->persist($formation);

        $formation = $this->createLocalFormation(self::FORMATION_5_UUID, $referent92, $zoneDepartment92, 'Deuxième formation du 92');
        $formation->setPosition(2);
        $formation->setContentType(FormationContentTypeEnum::LINK);
        $formation->setLink('http://renaissance.code/');
        $manager->persist($formation);

        /** @var Adherent $referent06 */
        $referent06 = $this->getReference('renaissance-user-3', Adherent::class);
        /** @var Zone $zoneDepartment06 */
        $zoneDepartment06 = LoadGeoZoneData::getZoneReference($manager, 'zone_department_06');

        $formation = $this->createLocalFormation(self::FORMATION_6_UUID, $referent06, $zoneDepartment06, 'Première formation du 06');
        $formation->setPosition(1);
        $formation->setContentType(FormationContentTypeEnum::FILE);
        $this->createFile($formation);
        $manager->persist($formation);

        $manager->flush();
    }

    private function createNationalFormation(
        string $uuid,
        Administrator $creator,
        string $title,
        bool $description = true,
        bool $published = true,
    ): Formation {
        $formation = $this->createFormation($uuid, $title, $description, $published);
        $formation->setCreatedByAdministrator($creator);

        return $formation;
    }

    private function createLocalFormation(
        string $uuid,
        Adherent $creator,
        Zone $zone,
        string $title,
        bool $description = true,
        bool $published = true,
    ): Formation {
        $formation = $this->createFormation($uuid, $title, $description, $published);
        $formation->setCreatedByAdherent($creator);
        $formation->setZone($zone);

        return $formation;
    }

    private function createFormation(
        string $uuid,
        string $title,
        bool $description = true,
        bool $published = true,
    ): Formation {
        $formation = new Formation(Uuid::fromString($uuid));
        $formation->setTitle($title);
        $formation->setDescription($description ? $this->faker->text('200') : null);
        $formation->setPublished($published);

        return $formation;
    }

    private function createFile(Formation $formation): void
    {
        $formation->setFile(new UploadedFile(
            __DIR__.'/../adherent_formations/formation.pdf',
            'Formation.pdf',
            'application/pdf',
            null,
            true
        ));

        $this->formationHandler->handleFile($formation);
    }

    public function getDependencies(): array
    {
        return [
            LoadAdminData::class,
            LoadAdherentData::class,
            LoadGeoZoneData::class,
        ];
    }
}
