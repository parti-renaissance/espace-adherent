<?php

namespace App\JeMarche;

use App\Entity\Jecoute\News;
use Symfony\Component\Messenger\MessageBusInterface;

class JeMarcheDeviceNotifier
{
    private $bus;

    public function __construct(MessageBusInterface $bus)
    {
        $this->bus = $bus;
    }

    public function sendNewsNotification(News $news): void
    {
        $this->bus->dispatch(new NewsNotificationCommand($news->getUuid()));
    }
}
