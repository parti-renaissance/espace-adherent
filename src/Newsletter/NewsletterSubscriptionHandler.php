<?php

namespace AppBundle\Newsletter;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\NewsletterSubscription;
use Doctrine\ORM\EntityManager;
use Ramsey\Uuid\Uuid;
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
        $adherent = $this->manager->getRepository(Adherent::class)->findOneActiveByEmail($subscription->getEmail());
        if ($adherent) {
            $this->eventDispatcher->dispatch(Events::NOTIFICATION, new NewsletterEvent($subscription, $adherent));

            return;
        }

        $subscription = $this->prepareSubscription($subscription);
        $this->manager->persist($subscription);
        $this->manager->flush();

        $this->eventDispatcher->dispatch(Events::SUBSCRIBE, new NewsletterEvent($subscription));
    }

    public function confirm(NewsletterSubscription $subscription): void
    {
        $subscription->setConfirmedAt(new \DateTime());

        $this->manager->persist($subscription);
        $this->manager->flush();

        $this->eventDispatcher->dispatch(Events::CONFIRMATION, new NewsletterEvent($subscription));
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

    private function prepareSubscription(NewsletterSubscription $subscription): NewsletterSubscription
    {
        $this->manager->getFilters()->disable('softdeleteable');

        $softDeletedSubscription = $this->findSubscriptionByEmail($subscription->getEmail());

        $this->manager->getFilters()->enable('softdeleteable');

        if ($softDeletedSubscription) {
            if ($postalCode = $subscription->getPostalCode()) {
                $softDeletedSubscription->setPostalCode($postalCode);
            }

            $softDeletedSubscription->setConfirmedAt(null);
            $softDeletedSubscription->recover();
            $subscription = $softDeletedSubscription;
        }

        $subscription->setToken(Uuid::uuid4());
        if (null === $subscription->getUuid()) {
            $subscription->setUuid(Uuid::uuid4());
        }

        return $subscription;
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
