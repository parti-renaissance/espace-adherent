<?php

namespace AppBundle\Newsletter;

use AppBundle\Entity\NewsletterSubscription;
use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class NewsletterSubscriptionHandler
{
    private $manager;
    private $eventDispatcher;

    public function __construct(EntityManager $manager, EventDispatcherInterface $eventDispatcher)
    {
        $this->manager = $manager;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function subscribe(NewsletterSubscription $subscription): void
    {
        $subscription = $this->recoverSoftDeletedSubscription($subscription);

        $this->manager->persist($subscription);
        $this->manager->flush();

        $this->eventDispatcher->dispatch(Events::SUBSCRIBE, new NewsletterEvent($subscription));
    }

    public function unsubscribe(?string $email): void
    {
        $subscription = $this->findSubscriptionByEmail($email);

        if ($subscription) {
            $this->manager->remove($subscription);
            $this->manager->flush();

            $this->eventDispatcher->dispatch(Events::UNSUBSCRIBE, new NewsletterEvent($subscription));
        }
    }

    private function recoverSoftDeletedSubscription(NewsletterSubscription $subscription): NewsletterSubscription
    {
        $this->manager->getFilters()->disable('softdeleteable');

        $softDeletedSubscription = $this->findSubscriptionByEmail($subscription->getEmail());

        $this->manager->getFilters()->enable('softdeleteable');

        if (!$softDeletedSubscription) {
            return $subscription;
        }

        if ($postalCode = $subscription->getPostalCode()) {
            $softDeletedSubscription->setPostalCode($postalCode);
        }

        $softDeletedSubscription->recover();

        return $softDeletedSubscription;
    }

    private function findSubscriptionByEmail(?string $email): ?NewsletterSubscription
    {
        return $this
            ->manager
            ->getRepository(NewsletterSubscription::class)
            ->findOneBy(['email' => $email])
        ;
    }
}
