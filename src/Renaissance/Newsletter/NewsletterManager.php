<?php

declare(strict_types=1);

namespace App\Renaissance\Newsletter;

use App\Entity\Renaissance\NewsletterSubscription;
use App\Renaissance\Newsletter\Command\SendWelcomeMailCommand;
use App\Repository\Renaissance\NewsletterSubscriptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class NewsletterManager
{
    public function __construct(
        private readonly NewsletterSubscriptionRepository $newsletterRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly MessageBusInterface $bus,
    ) {
    }

    public function saveSubscription(SubscriptionRequest $subscriptionRequest): void
    {
        if ($this->newsletterRepository->findOneByEmail($subscriptionRequest->email)) {
            return;
        }

        $this->entityManager->persist($newsletter = NewsletterSubscription::create($subscriptionRequest));
        $this->entityManager->flush();

        $this->bus->dispatch(new SendWelcomeMailCommand($newsletter));
    }
}
