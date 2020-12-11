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

    public function sendNotification(News $news): void
    {
        $this->bus->dispatch(new NotificationCommand($news->getUuid()));
    }
}
