<?php

namespace App\DataFixtures\ORM;

use App\Entity\AdherentFormation\File;
use App\Entity\AdherentFormation\Formation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class LoadAdherentFormationData extends Fixture
{
    private Generator $faker;

    public function __construct()
    {
        $this->faker = Factory::create('fr_FR');
    }

    public function load(ObjectManager $manager)
    {
        $manager->persist($this->createFormation('Première formation', 'first-formation'));
        $manager->persist($this->createFormation('Formation sans description', 'second-formation'));
        $manager->persist($this->createFormation('Formation non visible', 'third-formation', false));

        $manager->flush();
    }

    private function createFormation(string $title, string $file, bool $visible = true): Formation
    {
        $formation = new Formation();
        $formation->setTitle($title);
        $formation->setDescription($this->faker->text('200'));
        $formation->setVisible($visible);
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
}
