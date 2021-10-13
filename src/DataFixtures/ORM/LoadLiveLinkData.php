<?php

namespace App\DataFixtures\ORM;

use App\Content\LiveLinkFactory;
use Doctrine\Persistence\ObjectManager;

class LoadLiveLinkData extends AbstractFixtures
{
    private $factory;

    public function __construct(LiveLinkFactory $factory)
    {
        $this->factory = $factory;
    }

    public function load(ObjectManager $manager)
    {
        $manager->persist($this->factory->createFromArray([
            'position' => 1,
            'title' => 'Guadeloupe',
            'link' => '/articles/actualites/outre-mer',
        ]));

        $manager->persist($this->factory->createFromArray([
            'position' => 2,
            'title' => 'Le candidat du travail',
            'link' => '/article/travail',
        ]));

        $manager->flush();
    }
}
