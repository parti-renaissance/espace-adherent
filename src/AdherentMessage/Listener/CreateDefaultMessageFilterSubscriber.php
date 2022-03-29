<?php

namespace App\AdherentMessage\Listener;

use App\AdherentMessage\AdherentMessageTypeEnum;
use App\AdherentMessage\Events;
use App\AdherentMessage\Filter\FilterFactory;
use App\AdherentMessage\MessageEvent;
use App\Entity\Adherent;
use App\Entity\AdherentMessage\AdherentMessageInterface;
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

        if (
            AdherentMessageInterface::SOURCE_API === $message->getSource()
            || !\in_array($message->getType(), [
                AdherentMessageTypeEnum::DEPUTY,
                AdherentMessageTypeEnum::REFERENT,
                AdherentMessageTypeEnum::SENATOR,
                AdherentMessageTypeEnum::LRE_MANAGER_ELECTED_REPRESENTATIVE,
                AdherentMessageTypeEnum::CANDIDATE,
                AdherentMessageTypeEnum::CORRESPONDENT,
            ], true)
        ) {
            return;
        }

        if (!($author = $message->getAuthor()) instanceof Adherent) {
            return;
        }

        $message->setFilter(FilterFactory::create($author, $message->getType()));
    }
}
