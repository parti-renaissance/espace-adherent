<?php

namespace AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class LoadLiveLinkData implements FixtureInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function load(ObjectManager $manager)
    {
        $factory = $this->container->get('app.content.live_link_factory');

        $manager->persist($factory->createFromArray([
            'position' => 1,
            'title' => 'Guadeloupe',
            'link' => '/article/outre-mer',
        ]));

        $manager->persist($factory->createFromArray([
            'position' => 2,
            'title' => 'Le candidat du travail',
            'link' => '/article/travail',
        ]));

        $manager->flush();
    }
}
