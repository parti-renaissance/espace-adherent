<?php

declare(strict_types=1);

namespace App\Newsletter;

use App\Repository\NewsletterSubscriptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class NewsletterSubscriptionHandler
{
    public function __construct(
        private readonly EntityManagerInterface $manager,
        private readonly NewsletterSubscriptionRepository $repository,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function unsubscribe(?string $email): void
    {
        $subscription = $this->repository->findOneByEmail($email);

        if ($subscription) {
            $this->manager->remove($subscription);
            $this->manager->flush();

            $this->eventDispatcher->dispatch(new NewsletterEvent($subscription), Events::UNSUBSCRIBE);
        }
    }
}
