<?php

namespace AppBundle\Committee\Serializer;

use AppBundle\Entity\Committee;
use AppBundle\Referent\ManagedAreaUtils;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use JMS\Serializer\EventDispatcher\Events;

class CommitteeSerializationSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            [
                'event' => Events::POST_SERIALIZE,
                'class' => Committee::class,
                'method' => 'onPostSerialize',
            ],
        ];
    }

    public function onPostSerialize(ObjectEvent $event)
    {
        $event->getVisitor()->addData('tags', [ManagedAreaUtils::getCodeFromCommittee($event->getObject())]);
    }
}
