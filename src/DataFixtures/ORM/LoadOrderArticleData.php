<?php

namespace App\DataFixtures\ORM;

use App\Content\MediaFactory;
use App\Content\OrderArticleFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\HttpFoundation\File\File;

class LoadOrderArticleData extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('fr_FR');

        $factory = $this->container->get(OrderArticleFactory::class);
        $mediaFactory = $this->container->get(MediaFactory::class);
        $storage = $this->container->get('app.storage');

        // Media
        $mediaFile = new File(__DIR__.'/../../../app/data/dist/guadeloupe.jpg');
        $storage->put('images/article.jpg', file_get_contents($mediaFile->getPathname()));
        $media = $mediaFactory->createFromFile('Order article image', 'order_article.jpg', $mediaFile);

        $manager->persist($media);
        $manager->flush();

        // Article
        $manager->persist($factory->createFromArray([
            'position' => 1,
            'title' => 'La première article sur la page des ordonnances',
            'slug' => 'premiere-article',
            'description' => 'La première article',
            'media' => $media,
            'displayMedia' => true,
            'published' => true,
            'publishedAt' => $faker->dateTimeThisDecade,
            'sections' => [$this->getReference('os001'), $this->getReference('os004')],
            'content' => file_get_contents(__DIR__.'/../content.md'),
            'amp_content' => file_get_contents(__DIR__.'/../content_amp.html'),
        ]));

        $manager->persist($factory->createFromArray([
            'position' => 2,
            'title' => 'Article en brouillon pour la page des ordonnances en brouillon',
            'slug' => 'brouillon',
            'description' => 'Brouillon',
            'media' => $media,
            'displayMedia' => true,
            'published' => false,
            'publishedAt' => $faker->dateTimeThisDecade,
            'sections' => [$this->getReference('os001')],
            'content' => file_get_contents(__DIR__.'/../content.md'),
            'amp_content' => file_get_contents(__DIR__.'/../content_amp.html'),
        ]));

        $manager->flush();
    }

    public function getDependencies()
    {
        return [LoadOrderSectionData::class];
    }
}
