<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\ArticleCategory;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\File\File;

class LoadArticleData implements FixtureInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('fr_FR');

        $factory = $this->container->get('app.content.article_factory');
        $mediaFactory = $this->container->get('app.content.media_factory');
        $storage = $this->container->get('app.storage');
        $em = $this->container->get('doctrine.orm.entity_manager');

        // Media
        $mediaFile = new File(__DIR__.'/../../../../app/data/dist/guadeloupe.jpg');
        $storage->put('images/article.jpg', file_get_contents($mediaFile->getPathname()));
        $media = $mediaFactory->createFromFile('Article image', 'article.jpg', $mediaFile);
        $em->persist($media);

        $manager->flush();

        // Categories
        $manager->persist($newsCategory = new ArticleCategory('Actualités', 'actualites', 1));
        $manager->persist($videosCategory = new ArticleCategory('Vidéos', 'videos', 2));
        $manager->persist($speechCategory = new ArticleCategory('Discours', 'discours', 3));
        $manager->persist($mediasCategory = new ArticleCategory('Médias', 'medias', 4));
        $manager->persist($communiquesCategory = new ArticleCategory('Communiqués', 'communiques', 5));

        $manager->flush();

        // Article
        $manager->persist($factory->createFromArray([
            'title' => '« Les outre-mer sont l’un des piliers de notre richesse culturelle. »',
            'slug' => 'outre-mer',
            'description' => 'outre-mer',
            'media' => $media,
            'displayMedia' => true,
            'published' => true,
            'publishedAt' => $faker->dateTimeThisDecade,
            'category' => $newsCategory,
            'content' => file_get_contents(__DIR__.'/../content.md'),
        ]));

        $manager->persist($factory->createFromArray([
            'title' => 'Article en brouillon',
            'slug' => 'brouillon',
            'description' => 'brouillon',
            'media' => $media,
            'displayMedia' => true,
            'published' => false,
            'publishedAt' => $faker->dateTimeThisDecade,
            'category' => $newsCategory,
            'content' => file_get_contents(__DIR__.'/../content.md'),
        ]));

        $manager->persist($factory->createFromArray([
            'title' => 'Article image cachée',
            'slug' => 'sans-image',
            'description' => 'sans-image',
            'media' => $media,
            'displayMedia' => false,
            'published' => true,
            'publishedAt' => $faker->dateTimeThisDecade,
            'category' => $speechCategory,
            'content' => file_get_contents(__DIR__.'/../content.md'),
        ]));

        // A lot of articles for listing
        foreach ([$newsCategory, $videosCategory, $speechCategory, $mediasCategory, $communiquesCategory] as $category) {
            for ($i = 0; $i < 150; ++$i) {
                $manager->persist($factory->createFromArray([
                    'title' => $faker->sentence(),
                    'slug' => $faker->slug(),
                    'description' => $faker->text(),
                    'media' => $media,
                    'displayMedia' => false,
                    'published' => true,
                    'publishedAt' => $faker->dateTimeThisDecade,
                    'category' => $category,
                    'content' => file_get_contents(__DIR__.'/../content.md'),
                ]));
            }
        }

        $manager->flush();
    }
}
