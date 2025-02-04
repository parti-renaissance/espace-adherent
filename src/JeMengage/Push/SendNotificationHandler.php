<?php

namespace App\JeMengage\Push;

use App\Entity\Action\Action;
use App\Entity\EntityScopeVisibilityWithZoneInterface;
use App\Entity\EntityScopeVisibilityWithZonesInterface;
use App\Entity\Event\Event;
use App\Entity\Geo\Zone;
use App\Entity\Jecoute\News;
use App\Entity\NotificationObjectInterface;
use App\Entity\ZoneableEntityInterface;
use App\Firebase\JeMarcheMessaging;
use App\Firebase\Notification\NotificationInterface;
use App\Repository\PushTokenRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class SendNotificationHandler
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly JeMarcheMessaging $messaging,
        private readonly PushTokenRepository $pushTokenRepository,
        private readonly NotificationFactory $notificationFactory,
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

        $tokens = $this->findTokensForNotification($notification, $object);

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

    private function findTokensForNotification(NotificationInterface $notification, NotificationObjectInterface $object): array
    {
        // National notification for News or Event
        if (
            ($notification instanceof Notification\NewsCreatedNotification && $object instanceof News && $object->isNationalVisibility())
            || ($object instanceof Event && $object->national)
        ) {
            $notification->setScope('national');

            return $this->pushTokenRepository->findAllForNational();
        }

        if (
            Notification\ActionCreatedNotification::class === $notification::class
            || ($notification instanceof Notification\NewsCreatedNotification && $object instanceof News && !$object->getCommittee())
            || ($notification instanceof Notification\EventCreatedNotification && $object instanceof Event && !$object->getCommittee())
        ) {
            /** @var Zone[] $zones */
            $zones = [];
            if ($object instanceof EntityScopeVisibilityWithZoneInterface) {
                $zones = array_filter([$object->getZone()]);
            } elseif ($object instanceof EntityScopeVisibilityWithZonesInterface || $object instanceof ZoneableEntityInterface) {
                $zones = $object->getZones()->toArray();
            }

            $assemblyZone = null;

            foreach ($zones as $zone) {
                if ($assemblyZone = $zone->getAssemblyZone()) {
                    break;
                }
            }

            if (!$assemblyZone) {
                throw new \RuntimeException(\sprintf('Zone is required for notification %s', $notification::class));
            }

            $notification->setScope('zone:'.$assemblyZone->getCode());

            return $this->pushTokenRepository->findAllForZone($assemblyZone);
        }

        if (\in_array($notification::class, [
            Notification\ActionBeginNotification::class,
            Notification\ActionCancelledNotification::class,
            Notification\ActionUpdatedNotification::class,
            Notification\EventCreatedNotification::class,
            Notification\EventReminderNotification::class,
            Notification\NewsCreatedNotification::class,
        ], true)) {
            if (
                ($object instanceof Event && $object->getCommittee())
                || $object instanceof News
            ) {
                $notification->setScope('committee:'.$object->getCommittee()->getId());
            } else {
                $notification->setScope(match ($object::class) {
                    Event::class => 'event:'.$object->getId(),
                    Action::class => 'action:'.$object->getId(),
                    default => null,
                });
            }

            return $this->pushTokenRepository->findAllForNotificationObject($object);
        }

        return [];
    }
}
