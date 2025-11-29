<?php

declare(strict_types=1);

namespace App\Jecoute;

use App\Entity\Jecoute\News;
use App\JeMengage\Push\Command\NewsCreatedNotificationCommand;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class NewsHandler
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MessageBusInterface $bus,
    ) {
    }

    public function handleNotification(News $news): void
    {
        if (!$news->isNotification()) {
            return;
        }

        $this->bus->dispatch(new NewsCreatedNotificationCommand($news->getUuid()));
    }

    public function publish(News $news): void
    {
        $news->setPublished(true);

        $this->entityManager->flush();
    }

    public function unpublish(News $news): void
    {
        $news->setPublished(false);

        $this->entityManager->flush();
    }
}
