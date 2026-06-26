<?php

declare(strict_types=1);

namespace App\JeMengage\Push;

use App\Entity\Action\Action;
use App\Entity\AdherentMessage\AdherentMessage;
use App\Entity\EntityScopeVisibilityWithZoneInterface;
use App\Entity\EntityScopeVisibilityWithZonesInterface;
use App\Entity\Event\Event;
use App\Entity\NotificationObjectInterface;
use App\Entity\Pronostic\Pronostic;
use App\Entity\ZoneableEntityInterface;
use App\Firebase\Notification\MulticastNotificationInterface;
use App\JeMengage\Push\Command\SendNotificationCommandInterface;
use App\Repository\PushTokenRepository;
use App\Scope\ScopeEnum;

class TokenProviderResolver
{
    public function __construct(private readonly PushTokenRepository $pushTokenRepository)
    {
    }

    public function getTokens(MulticastNotificationInterface $notification, NotificationObjectInterface $object, SendNotificationCommandInterface $command): array
    {
        $scope = $notification->getScope();

        return match (true) {
            NotificationScope::PREFIX_NATIONAL === $scope => $this->pushTokenRepository->findAllForNational(),
            str_starts_with($scope, NotificationScope::PREFIX_ZONE) => $this->getZoneTokens($object),
            str_starts_with($scope, NotificationScope::PREFIX_PUBLICATION) => $this->getAudienceFilterTokens($object),
            str_starts_with($scope, NotificationScope::PREFIX_PRONOSTIC_NON_PARTICIPANTS) => $object instanceof Pronostic ? $this->pushTokenRepository->findAllForNonParticipants($object) : [],
            str_starts_with($scope, NotificationScope::PREFIX_PRONOSTIC_PARTICIPANTS) => $object instanceof Pronostic ? $this->pushTokenRepository->findAllForPronosticParticipants($object) : [],
            str_starts_with($scope, NotificationScope::PREFIX_COMMITTEE),
            str_starts_with($scope, NotificationScope::PREFIX_EVENT),
            str_starts_with($scope, NotificationScope::PREFIX_ACTION),
            str_starts_with($scope, NotificationScope::PREFIX_MEETING),
            str_starts_with($scope, NotificationScope::PREFIX_PRIVATE_MESSAGE) => $this->pushTokenRepository->findAllForNotificationObject($object, $command),
            default => throw new \RuntimeException(\sprintf('Unsupported notification scope "%s".', $scope)),
        };
    }

    private function getZoneTokens(NotificationObjectInterface $object): array
    {
        $zones = [];

        if ($object instanceof EntityScopeVisibilityWithZoneInterface) {
            $zones = array_filter([$object->getZone()]);
        } elseif ($object instanceof EntityScopeVisibilityWithZonesInterface || $object instanceof ZoneableEntityInterface) {
            $zones = $object->getZones()->toArray();
        }

        // An action targets its commune directly, like a militant event — getZones() may also hold
        // parent zones (department, region), so the commune-level zone is selected explicitly. For
        // Paris/Lyon/Marseille that zone is the arrondissement (borough), not the parent city.
        if ($object instanceof Action) {
            $communeZones = $object->getCityOrBoroughZones();

            return $communeZones ? $this->pushTokenRepository->findAllForZone(reset($communeZones)) : [];
        }

        // A militant event targets its city zone directly, without climbing to the assembly (department) zone.
        if ($object instanceof Event && ScopeEnum::MILITANT === $object->getAuthorScope()) {
            return $zones ? $this->pushTokenRepository->findAllForZone(reset($zones)) : [];
        }

        $assemblyZone = null;
        foreach ($zones as $zone) {
            if ($assemblyZone = $zone->getAssemblyZone()) {
                break;
            }
        }

        if (!$assemblyZone) {
            return [];
        }

        return $this->pushTokenRepository->findAllForZone($assemblyZone);
    }

    private function getAudienceFilterTokens(NotificationObjectInterface $object): array
    {
        if ($object instanceof AdherentMessage) {
            return $this->pushTokenRepository->findAllForAdherentMessage($object);
        }

        return [];
    }
}
