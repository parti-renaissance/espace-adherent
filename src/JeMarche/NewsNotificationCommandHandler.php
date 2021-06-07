<?php

namespace App\JeMarche;

use App\Entity\Jecoute\News;
use App\Firebase\JeMarcheMessaging;
use App\Jecoute\NewsTitlePrefix;
use App\Repository\Jecoute\NewsRepository;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class NewsNotificationCommandHandler implements MessageHandlerInterface
{
    private $newsRepository;
    private $messaging;
    private $newsTitlePrefix;

    public function __construct(
        NewsRepository $newsRepository,
        JeMarcheMessaging $messaging,
        NewsTitlePrefix $newsTitlePrefix
    ) {
        $this->newsRepository = $newsRepository;
        $this->messaging = $messaging;
        $this->newsTitlePrefix = $newsTitlePrefix;
    }

    public function __invoke(NewsNotificationCommand $command): void
    {
        $news = $this->getNews($command->getUuid());

        if (!$news) {
            return;
        }

        $title = $this->newsTitlePrefix->prefixTitle($news);
        $this->messaging->sendNotificationToTopic($news->getTopic(), $title, $news->getText());
    }

    private function getNews(UuidInterface $uuid): ?News
    {
        return $this->newsRepository->findOneByUuid($uuid);
    }
}
