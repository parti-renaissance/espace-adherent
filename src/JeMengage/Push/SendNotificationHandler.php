<?php

declare(strict_types=1);

namespace App\JeMengage\Push;

use App\Entity\NotificationObjectInterface;
use App\Entity\PushNotification;
use App\Firebase\JeMarcheMessaging;
use App\JeMengage\Push\Command\SendNotificationCommandInterface;
use App\JeMengage\Push\Command\SendPushChunkCommand;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class SendNotificationHandler
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly NotificationFactory $notificationFactory,
        private readonly TokenProviderResolver $tokenProviderResolver,
        private readonly MessageBusInterface $bus,
    ) {
    }

    public function __invoke(SendNotificationCommandInterface $command): void
    {
        if (!$object = $this->getObjectFromCommand($command)) {
            return;
        }

        if (!$object->isNotificationEnabled($command)) {
            return;
        }

        $notification = $this->notificationFactory->create($object, $command);

        $tokens = $this->tokenProviderResolver->getTokens($notification, $object, $command);

        if (empty($tokens)) {
            return;
        }

        sort($tokens);

        $parts = explode('\\', $notification::class);
        $notificationClassName = end($parts);

        $chunks = array_chunk($tokens, JeMarcheMessaging::MULTICAST_MAX_TOKENS);

        $pushNotification = new PushNotification(
            $notificationClassName,
            $notification->getTitle(),
            $notification->getBody(),
            $notification->getScope(),
            $notification->getData(),
            \count($chunks),
        );
        $this->entityManager->persist($pushNotification);
        $this->entityManager->flush();

        foreach ($chunks as $index => $chunk) {
            $this->bus->dispatch(new SendPushChunkCommand(
                $notificationClassName,
                $notification->getTitle(),
                $notification->getBody(),
                $notification->getScope(),
                $notification->getData(),
                $chunk,
                \sprintf('%s:%s:%s:push:%d', $command->getClass(), $command->getUuid()->toRfc4122(), $notificationClassName, $index),
                $pushNotification->getUuid(),
            ));
        }

        $object->handleNotificationSent($command);

        $this->entityManager->flush();
    }

    private function getObjectFromCommand(SendNotificationCommandInterface $command): ?NotificationObjectInterface
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
}
