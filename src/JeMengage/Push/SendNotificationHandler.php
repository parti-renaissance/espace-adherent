<?php

declare(strict_types=1);

namespace App\JeMengage\Push;

use App\Entity\NotificationObjectInterface;
use App\Firebase\JeMarcheMessaging;
use App\Firebase\Notification\NotificationInterface;
use App\JeMengage\Push\TokenProvider\TokenProviderInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class SendNotificationHandler
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly NotificationFactory $notificationFactory,
        private readonly iterable $tokenProviders,
        private readonly MessageBusInterface $bus,
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

        if (empty($tokens)) {
            return;
        }

        sort($tokens);

        $parts = explode('\\', $notification::class);
        $notificationClassName = end($parts);

        foreach (array_chunk($tokens, JeMarcheMessaging::MULTICAST_MAX_TOKENS) as $index => $chunk) {
            $this->bus->dispatch(new Command\SendPushChunkCommand(
                $notificationClassName,
                $notification->getTitle(),
                $notification->getBody(),
                $notification->getScope(),
                $notification->getData(),
                $chunk,
                \sprintf('%s:%s:%s:push:%d', $command->getClass(), $command->getUuid()->toString(), $notificationClassName, $index),
            ));
        }

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
     * @return string[]
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
