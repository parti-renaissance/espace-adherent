<?php

namespace AppBundle\Event\Serializer;

use AppBundle\Entity\Event;
use AppBundle\Referent\ManagedAreaUtils;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use JMS\Serializer\EventDispatcher\Events;

class EventSerializationSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            [
                'event' => Events::POST_SERIALIZE,
                'class' => Event::class,
                'method' => 'onPostSerialize',
            ],
        ];
    }

    public function onPostSerialize(ObjectEvent $event)
    {
        $tag = ManagedAreaUtils::getCodeFromEvent($event->getObject());

        $event->getVisitor()->addData('tags', $tag ? [$tag] : []);
    }
}
