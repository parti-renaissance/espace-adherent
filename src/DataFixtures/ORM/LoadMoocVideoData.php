<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\Mooc\AttachmentLink;
use AppBundle\Entity\Mooc\Video;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadMoocVideoData extends AbstractFixture
{
    public function load(ObjectManager $manager)
    {
        $video1 = new Video(
            'Les produits transformés',
            'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et 
            dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip 
            ex ea commodo consequat.',
            'ktHEfEDhscU'
        );

        $video1->addLink(
            new AttachmentLink('Site officiel de La République En Marche', 'http://www.en-marche.fr')
        );
        $video1->addLink(
            new AttachmentLink('Les sites départementaux de La République En Marche', 'http://dpt.en-marche.fr')
        );

        $manager->persist($video1);
        $this->addReference('mooc-video-1', $video1);

        $video2 = new Video(
            'Les produits transformés dans une deuxième vidéo',
            'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et 
            dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip 
            ex ea commodo consequat.',
            'ktHEfEDhscU'
        );
        $manager->persist($video2);
        $this->addReference('mooc-video-2', $video2);

        $video3 = new Video(
            'Les produits transformés dans une troisième vidéo',
            'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et 
            dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip 
            ex ea commodo consequat.',
            'ktHEfEDhscU'
        );

        $manager->persist($video3);
        $this->addReference('mooc-video-3', $video3);

        $manager->flush();
    }
}
