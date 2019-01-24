<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Content\MediaFactory;
use AppBundle\Entity\Formation\Article;
use AppBundle\Entity\Formation\Axe;
use AppBundle\Entity\Media;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\HttpFoundation\File\File;

class LoadFormationData extends Fixture
{
    private $faker;

    public function __construct()
    {
        $this->faker = Factory::create('fr_FR');
    }

    public function load(ObjectManager $manager)
    {
        $mediaFactory = $this->container->get(MediaFactory::class);
        $storage = $this->container->get('app.storage');

        // Media
        $mediaFile = new File(__DIR__.'/../../../app/data/dist/10decembre.jpg');
        $storage->put('images/espace-formation.jpg', file_get_contents($mediaFile->getPathname()));
        $media = $mediaFactory->createFromFile('Image pour l\'espace formation', 'espace-formation.jpg', $mediaFile);

        $manager->persist($axe1 = $this->createAxe('Premier axe', $media));
        $manager->persist($axe2 = $this->createAxe('Deuxième axe', $media));

        $manager->persist($this->createArticle($axe1, 'Premier article du premier axe', $media));
        $manager->persist($this->createArticle($axe1, 'Deuxième article du premier axe', $media));
        $manager->persist($this->createArticle($axe2, 'Premier article du deuxième axe', $media));
        $manager->persist($this->createArticle($axe2, 'Deuxième article du deuxième axe', $media));

        $manager->flush();
    }

    private function createAxe(string $title, Media $media): Axe
    {
        $axe = new Axe();
        $axe->setTitle($title);
        $axe->setDescription($this->faker->text('200'));
        $axe->setMedia($media);
        $axe->setDisplayMedia(true);
        $axe->setContent(file_get_contents(__DIR__.'/../content.md'));

        return $axe;
    }

    private function createArticle(Axe $axe, string $title, Media $media): Article
    {
        $article = new Article();
        $article->setAxe($axe);
        $article->setTitle($title);
        $article->setDescription($this->faker->text(200));
        $article->setMedia($media);
        $article->setDisplayMedia(true);
        $article->setContent(file_get_contents(__DIR__.'/../content.md'));

        return $article;
    }
}
