<?php

namespace App\DataFixtures\ORM;

use App\Content\LiveLinkFactory;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class LoadLiveLinkData implements FixtureInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function load(ObjectManager $manager)
    {
        $factory = $this->container->get(LiveLinkFactory::class);

        $manager->persist($factory->createFromArray([
            'position' => 1,
            'title' => 'Guadeloupe',
            'link' => '/articles/actualites/outre-mer',
        ]));

        $manager->persist($factory->createFromArray([
            'position' => 2,
            'title' => 'Le candidat du travail',
            'link' => '/article/travail',
        ]));

        $manager->flush();
    }
}
