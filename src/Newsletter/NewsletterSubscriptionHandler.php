<?php

namespace App\Newsletter;

use App\Entity\Adherent;
use App\Entity\NewsletterSubscription;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class NewsletterSubscriptionHandler
{
    private $manager;
    private $eventDispatcher;

    public function __construct(EntityManagerInterface $manager, EventDispatcherInterface $eventDispatcher)
    {
        $this->manager = $manager;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function subscribe(NewsletterSubscription $subscription): void
    {
        $adherent = $this->manager->getRepository(Adherent::class)->findOneActiveByEmail($subscription->getEmail());
        if ($adherent) {
            $this->eventDispatcher->dispatch(new NewsletterEvent($subscription, $adherent), Events::NOTIFICATION);

            return;
        }

        $subscription = $this->prepareSubscription($subscription);
        $this->manager->persist($subscription);
        $this->manager->flush();

        $this->eventDispatcher->dispatch(new NewsletterEvent($subscription), Events::SUBSCRIBE);
    }

    public function confirm(NewsletterSubscription $subscription): void
    {
        $subscription->setConfirmedAt(new \DateTime());

        $this->manager->persist($subscription);
        $this->manager->flush();

        $this->eventDispatcher->dispatch(new NewsletterEvent($subscription), Events::CONFIRMATION);
    }

    public function unsubscribe(?string $email): void
    {
        $subscription = $this->findSubscriptionByEmail($email);

        if ($subscription) {
            $this->manager->remove($subscription);
            $this->manager->flush();

            $this->eventDispatcher->dispatch(new NewsletterEvent($subscription), Events::UNSUBSCRIBE);
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
