<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\NewsletterSubscription;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class LoadNeswletterSubscriptionData implements FixtureInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @var EntityManager
     */
    private $em;

    public function load(ObjectManager $manager)
    {
        $this->em = $this->container->get('doctrine.orm.entity_manager');

        $subscription = new NewsletterSubscription();
        $subscription->setEmail('foobar@en-marche.fr');
        $subscription->setPostalCode('35420');
        $subscription->setClientIp('127.0.0.1');

        $this->em->persist($subscription);
        $this->em->flush();
    }
}
