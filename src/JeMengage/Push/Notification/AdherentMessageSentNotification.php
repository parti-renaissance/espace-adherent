<?php

declare(strict_types=1);

namespace App\JeMengage\Push\Notification;

use App\Entity\AdherentMessage\AdherentMessage;
use App\Firebase\Notification\AbstractMulticastNotification;

class AdherentMessageSentNotification extends AbstractMulticastNotification
{
    public static function create(AdherentMessage $adherentMessage): self
    {
        return new self($adherentMessage->getFromName(false), $adherentMessage->getSubject() ?? '');
    }
}
