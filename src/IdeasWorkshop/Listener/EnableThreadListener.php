<?php

namespace App\IdeasWorkshop\Listener;

use App\Entity\IdeasWorkshop\BaseComment;
use App\Entity\IdeasWorkshop\Thread;
use App\Entity\IdeasWorkshop\ThreadComment;
use App\IdeasWorkshop\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class EnableThreadListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            Events::THREAD_ENABLE => 'onEnableStatusChange',
            Events::THREAD_DISABLE => 'onEnableStatusChange',
            Events::THREAD_COMMENT_ENABLE => 'onEnableStatusChange',
            Events::THREAD_COMMENT_DISABLE => 'onEnableStatusChange',
        ];
    }

    public function onEnableStatusChange(GenericEvent $event): void
    {
        /** @var Thread|ThreadComment $object */
        $object = $event->getSubject();

        if ($object->isEnabled()) {
            $object->getIdea()->incrementCommentsCount($this->getIncrementValue($object));
        } else {
            $object->getIdea()->decrementCommentsCount($this->getIncrementValue($object));
        }
    }

    private function getIncrementValue(BaseComment $object): int
    {
        if ($object instanceof Thread) {
            return $object->countEnabledComment() + 1;
        }

        return 1;
    }
}
