<?php

declare(strict_types=1);

namespace App\JeMengage\Push\TokenProvider;

use App\Entity\EntityScopeVisibilityWithZoneInterface;
use App\Entity\EntityScopeVisibilityWithZonesInterface;
use App\Entity\Event\Event;
use App\Entity\Jecoute\News;
use App\Entity\NotificationObjectInterface;
use App\Entity\ZoneableEntityInterface;
use App\Firebase\Notification\NotificationInterface;
use App\JeMengage\Push\Command\SendNotificationCommandInterface;
use App\JeMengage\Push\Notification\ActionCreatedNotification;
use App\JeMengage\Push\Notification\EventCreatedNotification;
use App\JeMengage\Push\Notification\NewsCreatedNotification;

class ZoneTokenProvider extends AbstractTokenProvider
{
    public function supports(NotificationInterface $notification, NotificationObjectInterface $object): bool
    {
        return ActionCreatedNotification::class === $notification::class
            || ($notification instanceof NewsCreatedNotification && $object instanceof News && !$object->getCommittee())
            || ($notification instanceof EventCreatedNotification && $object instanceof Event && !$object->getCommittee());
    }

    public function getTokens(NotificationInterface $notification, NotificationObjectInterface $object, SendNotificationCommandInterface $command): array
    {
        if ($object->isNational()) {
            $notification->setScope('zone:national');

            return $this->pushTokenRepository->findAllForNational();
        }

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
}
