<?php

use AppBundle\Entity\Adherent;
use AppBundle\Membership\UserEvent;
use Behat\Behat\Context\Context;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class SyncWithAPIContext implements Context
{
    private $doctrine;
    private $dispatcher;

    public function __construct(Registry $doctrine, EventDispatcherInterface $dispatcher)
    {
        $this->doctrine = $doctrine;
        $this->dispatcher = $dispatcher;
    }

    /**
     * @When I dispatch the :event user event with :email
     */
    public function iDispatchUserEvent(string $event, string $email)
    {
        $adherent = $this->doctrine->getRepository(Adherent::class)->findOneBy(['emailAddress' => $email]);

        $this->dispatcher->dispatch($event, new UserEvent($adherent));
    }

    /**
     * @When I dispatch the :event user event with :email and email subscriptions
     */
    public function iDispatchUserEventWithEmailSubscriptions(string $event, string $email)
    {
        $adherent = $this->doctrine->getRepository(Adherent::class)->findOneBy(['emailAddress' => $email]);

        $subscriptions = $adherent->getSubscriptionTypes();
        if (!isset($subscriptions[0])) {
            throw new \Exception('User has no email subscription.');
        }
        unset($subscriptions[0]);

        $this->dispatcher->dispatch($event, new UserEvent($adherent, null, null, $subscriptions));
    }
}
