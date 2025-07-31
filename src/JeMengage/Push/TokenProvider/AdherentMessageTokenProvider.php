<?php

namespace App\JeMengage\Push\TokenProvider;

use App\Entity\AdherentMessage\AdherentMessage;
use App\Entity\NotificationObjectInterface;
use App\Firebase\Notification\NotificationInterface;
use App\JeMengage\Push\Command\SendNotificationCommandInterface;
use App\JeMengage\Push\Notification\AdherentMessageSentNotification;
use App\Repository\Projection\ManagedUserRepository;
use App\Repository\PushTokenRepository;

class AdherentMessageTokenProvider extends AbstractTokenProvider
{
    public function __construct(
        protected readonly PushTokenRepository $pushTokenRepository,
        private readonly ManagedUserRepository $managedUserRepository,
    ) {
        parent::__construct($pushTokenRepository);
    }

    public function supports(NotificationInterface $notification, NotificationObjectInterface $object): bool
    {
        return $notification instanceof AdherentMessageSentNotification && $object instanceof AdherentMessage;
    }

    /** @param AdherentMessage $object */
    public function getTokens(NotificationInterface $notification, NotificationObjectInterface $object, SendNotificationCommandInterface $command): array
    {
        $notification->setScope('adherent_message');

        $qb = $this->managedUserRepository->createAdherentMessageQueryBuilder($object);

        return $this->pushTokenRepository->findAllForAdherentMessage($qb);
    }
}
