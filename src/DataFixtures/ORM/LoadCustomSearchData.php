<?php

namespace App\DataFixtures\ORM;

use App\Content\CustomSearchResultFactory;
use App\Content\MediaFactory;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\File\File;

class LoadCustomSearchData implements FixtureInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function load(ObjectManager $manager)
    {
        $factory = $this->container->get(CustomSearchResultFactory::class);
        $mediaFactory = $this->container->get(MediaFactory::class);
        $storage = $this->container->get('app.storage');

        $description = 'Pour ceux qui sont convaincus que le pays est bloqué, qui ont le goût du travail, du progrès, '.
            'du risque, qui vivent pour la liberté, l\'égalité, et l\'Europe.';

        $mediaFile = new File(__DIR__.'/../../../app/data/dist/10decembre.jpg');
        $storage->put('images/custom_search.jpg', file_get_contents($mediaFile->getPathname()));
        $media = $mediaFactory->createFromFile('Custom search image', 'custom_search.jpg', $mediaFile);

        $manager->persist($media);
        $manager->flush();

        $manager->persist($factory->createFromArray([
            'keywords' => 'programme propositions',
            'title' => 'Le programme d\'Emmanuel Macron',
            'url' => '/emmanuel-macron/le-programme',
            'description' => 'Le moment que nous vivons est celui d’une refondation profonde de la France. Voici celle que nous vous proposons.',
            'media' => $media,
        ]));

        $manager->persist($factory->createFromArray([
            'keywords' => 'mouvement en marche nos valeurs',
            'title' => 'Le mouvement - Nos valeurs',
            'url' => '/le-mouvement',
            'description' => $description,
            'media' => $media,
        ]));

        $manager->flush();
    }
}
