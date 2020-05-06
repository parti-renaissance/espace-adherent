<?php

namespace App\DataFixtures\ORM;

use App\Content\ClarificationFactory;
use App\Content\MediaFactory;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\File\File;

class LoadClarificationData implements FixtureInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('fr_FR');

        $factory = $this->container->get(ClarificationFactory::class);
        $mediaFactory = $this->container->get(MediaFactory::class);
        $storage = $this->container->get('app.storage');

        // Media
        $mediaFile = new File(__DIR__.'/../../../app/data/dist/10decembre.jpg');
        $storage->put('images/clarification.jpg', file_get_contents($mediaFile->getPathname()));
        $media = $mediaFactory->createFromFile('Clarification image', 'clarification.jpg', $mediaFile);

        $manager->persist($media);
        $manager->flush();

        $manager->persist($factory->createFromArray([
            'title' => 'Héritier de Hollande ou traître du quinquennat ?',
            'slug' => 'heritier-hollande-traite-quiquennat',
            'description' => 'Description héritier de Hollande ou traître du quinquennat ?',
            'media' => $media,
            'displayMedia' => true,
            'published' => true,
            'content' => file_get_contents(__DIR__.'/../content.md'),
        ]));

        for ($i = 0; $i < 20; ++$i) {
            $manager->persist($factory->createFromArray([
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
