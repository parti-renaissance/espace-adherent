<?php

declare(strict_types=1);

namespace App\JeMengage\Push;

use App\Entity\NotificationObjectInterface;
use App\Entity\PushToken;
use App\Firebase\JeMarcheMessaging;
use App\Firebase\Notification\NotificationInterface;
use App\JeMengage\Push\TokenProvider\TokenProviderInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class SendNotificationHandler
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly JeMarcheMessaging $messaging,
        private readonly NotificationFactory $notificationFactory,
        private readonly iterable $tokenProviders,
    ) {
    }

    public function __invoke(Command\SendNotificationCommandInterface $command): void
    {
        if (!$object = $this->getObjectFromCommand($command)) {
            return;
        }

        if (!$object->isNotificationEnabled($command)) {
            return;
        }

        $notification = $this->notificationFactory->create($object, $command);

        $tokens = $this->findTokensForNotification($notification, $object, $command);
        sort($tokens);

        $notification->setTokens($tokens);

        $this->messaging->send($notification);

        $object->handleNotificationSent($command);

        $this->entityManager->flush();
    }

    private function getObjectFromCommand(Command\SendNotificationCommandInterface $command): ?NotificationObjectInterface
    {
        $object = $this->entityManager
            ->getRepository($command->getClass())
            ->findOneBy(['uuid' => $command->getUuid()])
        ;

        if (!$object) {
            return null;
        }

        $this->entityManager->refresh($object);

        return $object;
    }

    /**
     * @return PushToken[]
     */
    private function findTokensForNotification(NotificationInterface $notification, NotificationObjectInterface $object, Command\SendNotificationCommandInterface $command): array
    {
        /** @var TokenProviderInterface $provider */
        foreach ($this->tokenProviders as $provider) {
            if ($provider->supports($notification, $object)) {
                return $provider->getTokens($notification, $object, $command);
            }
        }

        return [];
    }
}
