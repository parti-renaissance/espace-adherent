<?php

namespace App\DataFixtures\ORM;

use App\Entity\Adherent;
use App\Entity\AdherentFormation\File;
use App\Entity\AdherentFormation\Formation;
use App\Entity\Administrator;
use App\Entity\Geo\Zone;
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

    private Generator $faker;

    public function __construct()
    {
        $this->faker = Factory::create('fr_FR');
    }

    public function load(ObjectManager $manager)
    {
        /** @var Administrator $administrator */
        $administrator = $this->getReference('administrator-renaissance');

        $manager->persist($this->createNationalFormation(self::FORMATION_1_UUID, $administrator, 'Première formation'));
        $manager->persist($this->createNationalFormation(self::FORMATION_2_UUID, $administrator, 'Formation sans description', false));
        $manager->persist($this->createNationalFormation(self::FORMATION_3_UUID, $administrator, 'Formation non publiée', true, false));

        /** @var Adherent $referent77 */
        $referent77 = $this->getReference('adherent-8');
        /** @var Zone $zoneDepartment77 */
        $zoneDepartment77 = LoadGeoZoneData::getZoneReference($manager, 'zone_department_77');

        $manager->persist($this->createLocalFormation(self::FORMATION_4_UUID, $referent77, $zoneDepartment77, 'Première formation du 77'));
        $manager->persist($this->createLocalFormation(self::FORMATION_5_UUID, $referent77, $zoneDepartment77, 'Deuxième formation du 77'));

        $manager->flush();
    }

    private function createNationalFormation(
        string $uuid,
        Administrator $creator,
        string $title,
        bool $description = true,
        bool $published = true
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
        bool $published = true
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
        bool $published = true
    ): Formation {
        $formation = new Formation(Uuid::fromString($uuid));
        $formation->setTitle($title);
        $formation->setDescription($description ? $this->faker->text('200') : null);
        $formation->setPublished($published);
        $formation->setFile($this->createFile($title));

        return $formation;
    }

    private function createFile(string $title): File
    {
        $file = new File();
        $file->setTitle($title);
        $file->setFile(new UploadedFile(
            __DIR__.'/../adherent_formations/formation.pdf',
            "$title.pdf",
            'application/pdf',
            null,
            true
        ));

        return $file;
    }

    public function getDependencies()
    {
        return [
            LoadAdminData::class,
            LoadAdherentData::class,
            LoadGeoZoneData::class,
        ];
    }
}
