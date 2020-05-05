<?php

namespace AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class LoadNewsletterSubscriptionData extends AbstractFixture implements ContainerAwareInterface, OrderedFixtureInterface
{
    use ContainerAwareTrait;

    public function load(ObjectManager $manager)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $factory = $this->container->get('app.newsletter_subscription.factory');

        $newsletterSubscription92 = $factory->create('abc@en-marche-dev.fr', '92110');
        $newsletterSubscription92->setConfirmedAt(new \DateTime('2020-08-03'));
        $this->addReference('news-sub-92', $newsletterSubscription92);

        $newsletterSubscription77 = $factory->create('def@en-marche-dev.fr', '77000');
        $this->addReference('news-sub-77', $newsletterSubscription77);

        $em->persist($factory->create('foobar@en-marche-dev.fr', '35420'));
        $em->persist($factory->create('referent@en-marche-dev.fr', '35420'));
        $em->persist($newsletterSubscription92);
        $em->persist($newsletterSubscription77);
        $em->persist($factory->create('ghi@en-marche-dev.fr', '77123'));

        $em->flush();
    }

    public function getOrder()
    {
        return 1;
    }
}
