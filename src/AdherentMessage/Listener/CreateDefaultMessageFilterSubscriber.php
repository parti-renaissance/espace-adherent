<?php

namespace AppBundle\AdherentMessage\Listener;

use AppBundle\AdherentMessage\AdherentMessageTypeEnum;
use AppBundle\AdherentMessage\Events;
use AppBundle\AdherentMessage\Filter\FilterFactory;
use AppBundle\AdherentMessage\MessageEvent;
use AppBundle\Entity\Adherent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CreateDefaultMessageFilterSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            Events::MESSAGE_PRE_CREATE => ['createDefaultMessageFilter', -1],
        ];
    }

    public function createDefaultMessageFilter(MessageEvent $event): void
    {
        $message = $event->getMessage();

        if (!\in_array($message->getType(), [AdherentMessageTypeEnum::DEPUTY], true)) {
            return;
        }

        if (!($author = $message->getAuthor()) instanceof Adherent) {
            return;
        }

        $message->setFilter(FilterFactory::create($author, $message->getType()));
    }
}
