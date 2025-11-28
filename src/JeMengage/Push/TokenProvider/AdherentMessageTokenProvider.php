<?php

declare(strict_types=1);

namespace App\JeMengage\Push\TokenProvider;

use App\Entity\AdherentMessage\AdherentMessage;
use App\Entity\NotificationObjectInterface;
use App\Firebase\Notification\NotificationInterface;
use App\JeMengage\Push\Command\SendNotificationCommandInterface;

class AdherentMessageTokenProvider extends AbstractTokenProvider
{
    public function supports(NotificationInterface $notification, NotificationObjectInterface $object): bool
    {
        return $object instanceof AdherentMessage;
    }

    /** @param AdherentMessage $object */
    public function getTokens(NotificationInterface $notification, NotificationObjectInterface $object, SendNotificationCommandInterface $command): array
    {
        $notification->setScope('publication:'.$object->getId());

        return $this->pushTokenRepository->findAllForAdherentMessage($object);
    }
}
