<?php

declare(strict_types=1);

namespace App\JeMengage\Push\TokenProvider;

use App\Entity\NotificationObjectInterface;
use App\Firebase\Notification\NotificationInterface;
use App\JeMengage\Push\Command\SendNotificationCommandInterface;

class NationalTokenProvider extends AbstractTokenProvider
{
    public function supports(NotificationInterface $notification, NotificationObjectInterface $object): bool
    {
        return $object->isNational();
    }

    public function getTokens(NotificationInterface $notification, NotificationObjectInterface $object, SendNotificationCommandInterface $command): array
    {
        $notification->setScope('national');

        return $this->pushTokenRepository->findAllForNational();
    }
}
