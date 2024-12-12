<?php

namespace App\JeMarche\Handler;

use App\Entity\Jecoute\News;
use App\Firebase\JeMarcheMessaging;
use App\Jecoute\NewsTitlePrefix;
use App\JeMarche\Command\NewsCreatedNotificationCommand;
use App\JeMarche\Notification\NewsCreatedNotification;
use App\Repository\Jecoute\NewsRepository;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class NewsCreatedNotificationCommandHandler
{
    private $newsRepository;
    private $messaging;
    private $newsTitlePrefix;

    public function __construct(
        NewsRepository $newsRepository,
        JeMarcheMessaging $messaging,
        NewsTitlePrefix $newsTitlePrefix,
    ) {
        $this->newsRepository = $newsRepository;
        $this->messaging = $messaging;
        $this->newsTitlePrefix = $newsTitlePrefix;
    }

    public function __invoke(NewsCreatedNotificationCommand $command): void
    {
        $news = $this->getNews($command->getUuid());

        if (!$news) {
            return;
        }

        $title = $this->newsTitlePrefix->prefixTitle($news);

        $this->messaging->send(NewsCreatedNotification::create($news, $title));
    }

    private function getNews(UuidInterface $uuid): ?News
    {
        return $this->newsRepository->findOneByUuid($uuid);
    }
}
