<?php

namespace App\AdherentMessage\Listener;

use App\AdherentMessage\AdherentMessageTypeEnum;
use App\AdherentMessage\Events;
use App\AdherentMessage\Filter\FilterFactory;
use App\AdherentMessage\MessageEvent;
use App\Entity\Adherent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CreateDefaultMessageFilterSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            Events::MESSAGE_PRE_CREATE => ['createDefaultMessageFilter', 1000],
        ];
    }

    public function createDefaultMessageFilter(MessageEvent $event): void
    {
        $message = $event->getMessage();

        if (!\in_array($message->getType(), [
            AdherentMessageTypeEnum::DEPUTY,
            AdherentMessageTypeEnum::REFERENT,
            AdherentMessageTypeEnum::SENATOR,
        ], true)) {
            return;
        }

        if (!($author = $message->getAuthor()) instanceof Adherent) {
            return;
        }

        $message->setFilter(FilterFactory::create($author, $message->getType()));
    }
}
