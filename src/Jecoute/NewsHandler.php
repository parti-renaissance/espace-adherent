<?php

namespace App\Jecoute;

use App\Entity\Jecoute\News;
use App\JeMarche\Command\NewsCreatedNotificationCommand;
use App\Repository\Jecoute\NewsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class NewsHandler
{
    public function __construct(
        private readonly NewsRepository $newsRepository,
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

    public function changePinned(News $news): void
    {
        if (!$news->isPinned()) {
            return;
        }

        $this->newsRepository->changePinned($news);
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
