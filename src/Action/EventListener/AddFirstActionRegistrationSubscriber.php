<?php

declare(strict_types=1);

namespace App\Action\EventListener;

use App\Action\ActionEvent;
use App\Action\RegisterManager;
use App\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AddFirstActionRegistrationSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly RegisterManager $registerManager)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::ACTION_CREATED => 'onActionCreated',
        ];
    }

    public function onActionCreated(ActionEvent $event): void
    {
        if ($author = $event->getAuthor()) {
            $this->registerManager->register($event->getAction(), $author);
        }
    }
}
