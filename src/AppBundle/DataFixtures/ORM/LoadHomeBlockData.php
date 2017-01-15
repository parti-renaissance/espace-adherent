<?php

namespace AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\File\File;

class LoadHomeBlockData implements FixtureInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function load(ObjectManager $manager)
    {
        $mediaFactory = $this->container->get('app.content.media_factory');
        $homeBlockFactory = $this->container->get('app.content.home_block_factory');

        $file = new File(__DIR__.'/../../../../tests/Fixtures/guadeloupe.jpg');
        $media = $mediaFactory->createFromFile('Guadeloupe', 'guadeloupe.jpg', $file);

        $banner = $homeBlockFactory->createFromArray([
            'position' => 0,
            'positionName' => 'Bannière',
            'type' => 'article',
            'title' => '« Je viens échanger, comprendre et construire. »',
            'subtitle' => 'Emmanuel Macron a scilloné la Guadeloupe, la Martinique et la Guyane pendant 5 jours.',
            'link' => '/article/outre-mer',
            'media' => $media,
        ]);

        $block1 = $homeBlockFactory->createFromArray([
            'position' => 1,
            'positionName' => 'Block 1',
            'type' => 'article',
            'title' => 'Tribune de Richard Ferrand',
            'subtitle' => null,
            'link' => '/article/outre-mer',
            'media' => $media,
        ]);

        $manager->persist($media);
        $manager->persist($banner);
        $manager->persist($block1);

        $manager->flush();
    }
}
