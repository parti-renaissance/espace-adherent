<?php

declare(strict_types=1);

namespace App\JeMengage\Push;

use App\Entity\Notification;
use App\Entity\PushNotification;
use App\Entity\PushToken;
use App\JeMengage\Push\Command\LinkNotificationPushTokensCommand;
use App\Repository\PushTokenRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class LinkNotificationPushTokensHandler
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly PushTokenRepository $pushTokenRepository,
    ) {
    }

    public function __invoke(LinkNotificationPushTokensCommand $command): void
    {
        $pushNotification = $this->entityManager->getRepository(PushNotification::class)
            ->findOneBy(['uuid' => $command->getUuid()]);

        if (!$pushNotification) {
            return;
        }

        $notifications = $this->entityManager->getRepository(Notification::class)
            ->findBy(['pushNotification' => $pushNotification]);

        foreach ($notifications as $notification) {
            $tokens = $notification->getTokens();

            if (empty($tokens)) {
                continue;
            }

            $idMap = $this->pushTokenRepository->findIdMapByIdentifiers($tokens);

            foreach ($idMap as $pushTokenId) {
                $notification->addPushToken(
                    $this->entityManager->getReference(PushToken::class, $pushTokenId)
                );
            }
        }

        $this->entityManager->flush();
    }
}
