<?php

namespace App\JeMengage\Push\Notification;

use App\Entity\AdherentMessage\AdherentMessage;
use App\Firebase\Notification\AbstractMulticastNotification;

class AdherentMessageSentNotification extends AbstractMulticastNotification
{
    public static function create(AdherentMessage $adherentMessage): self
    {
        return new self(
            $adherentMessage->getSubject(),
            $adherentMessage->getCleanedCroppedText(50),
        );
    }
}
