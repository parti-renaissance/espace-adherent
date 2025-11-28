<?php

declare(strict_types=1);

namespace App\Legislative\Newsletter;

use App\Entity\LegislativeNewsletterSubscription;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class LegislativeNewsletterSubscriptionHandler
{
    private EntityManagerInterface $manager;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(EntityManagerInterface $manager, EventDispatcherInterface $eventDispatcher)
    {
        $this->manager = $manager;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function confirm(LegislativeNewsletterSubscription $subscription): void
    {
        $subscription->setConfirmedAt(new \DateTime());

        $this->manager->persist($subscription);
        $this->manager->flush();

        $this->eventDispatcher->dispatch(new LegislativeNewsletterEvent($subscription), Events::NEWSLETTER_CONFIRMATION);
    }
}
