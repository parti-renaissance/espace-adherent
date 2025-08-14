<?php

namespace App\JeMengage\Push\Notification;

use App\Entity\AdherentMessage\AdherentMessage;
use App\Firebase\Notification\AbstractMulticastNotification;

class AdherentMessageSentNotification extends AbstractMulticastNotification
{
    public static function create(AdherentMessage $adherentMessage): self
    {
        if (!$sender = $adherentMessage->getSender()) {
            throw new \LogicException('Publication must have a sender to create a notification');
        }

        return new self($sender->getFullName(), $adherentMessage->getSubject() ?? '');
    }
}
