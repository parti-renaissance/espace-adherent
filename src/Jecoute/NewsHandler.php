<?php

namespace App\Jecoute;

use App\Entity\Jecoute\News;
use App\JeMarche\JeMarcheDeviceNotifier;
use App\JeMarche\NotificationTopicBuilder;
use App\Repository\Jecoute\NewsRepository;
use Doctrine\ORM\EntityManagerInterface;

class NewsHandler
{
    private NewsRepository $newsRepository;
    private EntityManagerInterface $entityManager;
    private JeMarcheDeviceNotifier $deviceNotifier;
    private NotificationTopicBuilder $topicBuilder;

    public function __construct(
        NewsRepository $newsRepository,
        EntityManagerInterface $entityManager,
        JeMarcheDeviceNotifier $deviceNotifier,
        NotificationTopicBuilder $topicBuilder,
    ) {
        $this->newsRepository = $newsRepository;
        $this->entityManager = $entityManager;
        $this->deviceNotifier = $deviceNotifier;
        $this->topicBuilder = $topicBuilder;
    }

    public function buildTopic(News $news): void
    {
        if (!$news->isNotification()) {
            return;
        }

        $topic = $this->topicBuilder->buildTopic($news->getZone());

        $news->setTopic($topic);
    }

    public function handleNotification(News $news): void
    {
        if (!$news->isNotification() || null === $news->getTopic()) {
            return;
        }

        $this->deviceNotifier->sendNewsNotification($news);
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
