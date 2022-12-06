<?php

namespace App\DataFixtures\ORM;

use App\Content\MediaFactory;
use App\Entity\AdherentFormation\File;
use App\Entity\AdherentFormation\Formation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use League\Flysystem\FilesystemInterface;

class LoadAdherentFormationData extends Fixture
{
    private $faker;

    public function __construct(MediaFactory $mediaFactory, FilesystemInterface $storage)
    {
        $this->mediaFactory = $mediaFactory;
        $this->storage = $storage;
        $this->faker = Factory::create('fr_FR');
    }

    public function load(ObjectManager $manager)
    {
        $manager->persist($this->createFormation('First formation', 'first-formation'));
        $manager->persist($this->createFormation('Second formation', 'second-formation'));

        $manager->flush();
    }

    private function createFormation(string $title, string $file): Formation
    {
        $formation = new Formation();
        $formation->setTitle($title);
        $formation->setDescription($this->faker->text('200'));
        $formation->setFile(new File(
            $title,
            $file,
            'pdf',
            'formation-file.pdf'
        ));

        return $formation;
    }
}
