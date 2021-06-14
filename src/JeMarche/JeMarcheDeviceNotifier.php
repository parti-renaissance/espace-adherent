<?php

namespace App\JeMarche;

use App\Entity\Event\BaseEvent;
use App\Entity\Event\CommitteeEvent;
use App\Entity\Event\DefaultEvent;
use App\Entity\Jecoute\News;
use App\JeMarche\Command\CommitteeEventCreationNotificationCommand;
use App\JeMarche\Command\DefaultEventCreationNotificationCommand;
use App\JeMarche\Command\EventReminderNotificationCommand;
use App\JeMarche\Command\NewsCreatedNotificationCommand;
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
        $this->bus->dispatch(new NewsCreatedNotificationCommand($news->getUuid()));
    }

    public function sendDefaultEventCreatedNotification(DefaultEvent $event): void
    {
        $this->bus->dispatch(new DefaultEventCreationNotificationCommand($event->getUuid()));
    }

    public function sendCommitteeEventCreatedNotification(CommitteeEvent $event): void
    {
        $this->bus->dispatch(new CommitteeEventCreationNotificationCommand($event->getUuid()));
    }

    public function sendEventReminder(BaseEvent $event): void
    {
        $this->bus->dispatch(new EventReminderNotificationCommand($event->getUuid()));
    }
}
