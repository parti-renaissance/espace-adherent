<?php

namespace App\JeMengage\Push\TokenProvider;

use App\Entity\Event\Event;
use App\Entity\Jecoute\News;
use App\Entity\NotificationObjectInterface;
use App\Firebase\Notification\NotificationInterface;
use App\JeMengage\Push\Command\SendNotificationCommandInterface;
use App\JeMengage\Push\Notification\NewsCreatedNotification;

class NationalTokenProvider extends AbstractTokenProvider
{
    public function supports(NotificationInterface $notification, NotificationObjectInterface $object): bool
    {
        return ($notification instanceof NewsCreatedNotification && $object instanceof News && $object->isNationalVisibility())
            || ($object instanceof Event && $object->isNational());
    }

    public function getTokens(NotificationInterface $notification, NotificationObjectInterface $object, SendNotificationCommandInterface $command): array
    {
        $notification->setScope('national');

        return $this->pushTokenRepository->findAllForNational();
    }
}
