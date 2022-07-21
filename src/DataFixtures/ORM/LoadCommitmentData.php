<?php

namespace App\DataFixtures\ORM;

use App\Entity\Commitment;
use App\Image\ImageManagerInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class LoadCommitmentData extends Fixture
{
    private ImageManagerInterface $imageManager;

    public function __construct(ImageManagerInterface $imageManager)
    {
        $this->imageManager = $imageManager;
    }

    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('fr_FR');

        for ($i = 1; $i <= 20; ++$i) {
            $manager->persist($object = new Commitment());
            $object->title = $faker->city();
            $object->shortDescription = $faker->paragraphs(random_int(1, 2), true);
            $object->description = $faker->paragraphs(random_int(6, 12), true);

            $object->setImage(new UploadedFile(
                __DIR__.'/../coalitions/default.png',
                'image.png',
                'image/png',
                null,
                true
            ));
            $this->imageManager->saveImage($object);
        }

        $manager->flush();
    }
}
