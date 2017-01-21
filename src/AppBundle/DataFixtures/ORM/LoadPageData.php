<?php

namespace AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class LoadPageData implements FixtureInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function load(ObjectManager $manager)
    {
        $factory = $this->container->get('app.content.article_factory');

        /*
         * $manager->persist($factory->createFromArray([
            'title' => '« Les outre-mer sont l’un des piliers de notre richesse culturelle. »',
            'slug' => 'outre-mer',
            'description' => 'outre-mer',
            'media' => null,
            'content' => file_get_contents(__DIR__.'/../../../../tests/Fixtures/content.md'),
        ]));
         */

        $manager->flush();
    }
}
