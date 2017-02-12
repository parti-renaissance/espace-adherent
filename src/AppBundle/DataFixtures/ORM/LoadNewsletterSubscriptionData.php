<?php

namespace AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class LoadNewsletterSubscriptionData implements FixtureInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function load(ObjectManager $manager)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $factory = $this->container->get('app.newsletter_subscription.factory');

        $em->persist($factory->create('foobar@en-marche-dev.fr', '35420'));
        $em->persist($factory->create('referent@en-marche-dev.fr', '35420'));
        $em->persist($factory->create('abc@en-marche-dev.fr', '92110'));
        $em->persist($factory->create('def@en-marche-dev.fr', '77000'));
        $em->persist($factory->create('ghi@en-marche-dev.fr', '77123'));

        $em->flush();
    }
}
