<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\Media;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\File\File;

class LoadArticleData implements FixtureInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function load(ObjectManager $manager)
    {
        $factory = $this->container->get('app.content.article_factory');
        $mediaFactory = $this->container->get('app.content.media_factory');
        $storage = $this->container->get('app.storage');
        $em = $this->container->get('doctrine.orm.entity_manager');

        $mediaFile = new File(__DIR__.'/../../../../app/data/dist/guadeloupe.jpg');
        $storage->put('images/article.jpg', file_get_contents($mediaFile->getPathname()));
        $media = $mediaFactory->createFromFile('Article image', 'article.jpg', $mediaFile);
        $em->persist($media);

        $manager->persist($factory->createFromArray([
            'title' => '« Les outre-mer sont l’un des piliers de notre richesse culturelle. »',
            'slug' => 'outre-mer',
            'description' => 'outre-mer',
            'media' => $media,
            'displayMedia' => true,
            'published' => true,
            'content' => file_get_contents(__DIR__.'/../../../../tests/Fixtures/content.md'),
        ]));

        $manager->persist($factory->createFromArray([
            'title' => 'Article en brouillon',
            'slug' => 'brouillon',
            'description' => 'brouillon',
            'media' => $media,
            'displayMedia' => true,
            'published' => false,
            'content' => file_get_contents(__DIR__.'/../../../../tests/Fixtures/content.md'),
        ]));

        $manager->persist($factory->createFromArray([
            'title' => 'Article image cachée',
            'slug' => 'sans-image',
            'description' => 'sans-image',
            'media' => $media,
            'displayMedia' => false,
            'published' => true,
            'content' => file_get_contents(__DIR__.'/../../../../tests/Fixtures/content.md'),
        ]));

        $manager->flush();
    }
}
