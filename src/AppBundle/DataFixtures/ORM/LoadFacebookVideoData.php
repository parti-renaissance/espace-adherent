<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\FacebookVideo;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class LoadFacebookVideoData implements FixtureInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function load(ObjectManager $manager)
    {
        $video = new FacebookVideo();
        $video->setUrl('https://www.facebook.com/EnMarche/videos/769283806583157/');
        $video->setDescription('Laurence Haïm a un message pour vous. Inscrivez-vous ➜ en-marche.fr/bercy 2');
        $video->setAuthor('En Marche 2');
        $video->setPosition(2);
        $video->setPublished(true);

        $manager->persist($video);

        $video2 = new FacebookVideo();
        $video2->setUrl('https://www.facebook.com/EnMarche/videos/769283806583157/');
        $video2->setDescription('Laurence Haïm a un message pour vous. Inscrivez-vous ➜ en-marche.fr/bercy');
        $video2->setAuthor('En Marche');
        $video2->setPosition(1);
        $video2->setPublished(true);

        $manager->persist($video2);

        $manager->flush();
    }
}
