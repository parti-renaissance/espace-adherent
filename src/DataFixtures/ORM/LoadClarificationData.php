<?php

namespace App\DataFixtures\ORM;

use App\Content\ClarificationFactory;
use App\Content\MediaFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\HttpFoundation\File\File;

class LoadClarificationData extends Fixture
{
    private $clarificationFactory;
    private $mediaFactory;
    private $storage;

    public function __construct(
        ClarificationFactory $clarificationFactory,
        MediaFactory $mediaFactory,
        FilesystemOperator $defaultStorage
    ) {
        $this->clarificationFactory = $clarificationFactory;
        $this->mediaFactory = $mediaFactory;
        $this->storage = $defaultStorage;
    }

    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('fr_FR');

        // Media
        $mediaFile = new File(__DIR__.'/../../../app/data/dist/10decembre.jpg');
        $this->storage->write('images/clarification.jpg', file_get_contents($mediaFile->getPathname()));
        $media = $this->mediaFactory->createFromFile('Clarification image', 'clarification.jpg', $mediaFile);

        $manager->persist($media);
        $manager->flush();

        $manager->persist($this->clarificationFactory->createFromArray([
            'title' => 'Héritier de Hollande ou traître du quinquennat ?',
            'slug' => 'heritier-hollande-traite-quiquennat',
            'description' => 'Description héritier de Hollande ou traître du quinquennat ?',
            'media' => $media,
            'displayMedia' => true,
            'published' => true,
            'content' => file_get_contents(__DIR__.'/../content.md'),
        ]));

        for ($i = 0; $i < 20; ++$i) {
            $manager->persist($this->clarificationFactory->createFromArray([
                'title' => $faker->sentence(),
                'slug' => $faker->slug(),
                'description' => $faker->text(),
                'media' => $media,
                'displayMedia' => false,
                'published' => true,
                'content' => file_get_contents(__DIR__.'/../content.md'),
            ]));
        }

        $manager->flush();
    }
}
