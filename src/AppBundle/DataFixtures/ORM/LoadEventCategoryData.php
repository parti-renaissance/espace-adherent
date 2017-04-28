<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\EventCategory;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class LoadEventCategoryData implements FixtureInterface, ContainerAwareInterface
{
    // Don't sort the list, they are used in the Doctrine migration 20170428100000
    // You can add more at the end
    const CATEGORIES = [
        'Kiosque',
        'Réunion d\'équipe',
        'Conférence-débat',
        'Porte-à-porte',
        'Atelier du programme',
        'Tractage',
        'Convivialité',
        'Action ciblée',
        'Événement innovant',
        'Marche',
        'Support party',
    ];

    use ContainerAwareTrait;

    public function load(ObjectManager $manager)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');

        foreach (self::CATEGORIES as $name) {
            $em->persist(new EventCategory($name));
        }

        $em->flush();
    }
}
