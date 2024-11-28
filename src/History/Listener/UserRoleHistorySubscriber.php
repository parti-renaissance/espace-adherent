<?php

namespace App\History\Listener;

use App\Entity\Adherent;
use App\Entity\Reporting\UserRoleHistory;
use App\History\AdministratorActionEvent;
use App\History\AdministratorActionEvents;
use App\History\Command\UserRoleHistoryCommand;
use App\Scope\GeneralScopeGenerator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class UserRoleHistorySubscriber implements EventSubscriberInterface
{
    private array $userRolesBeforeUpdate = [];

    public function __construct(
        private readonly MessageBusInterface $bus,
        private readonly GeneralScopeGenerator $generalScopeGenerator,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            AdministratorActionEvents::ADMIN_USER_PROFILE_BEFORE_UPDATE => ['onAdherentProfileBeforeUpdate', -4096],
            AdministratorActionEvents::ADMIN_USER_PROFILE_AFTER_UPDATE => ['onAdherentProfileAfterUpdate', -4096],
        ];
    }

    public function onAdherentProfileBeforeUpdate(AdministratorActionEvent $event): void
    {
        if (!$adherent = $event->adherent) {
            return;
        }

        $this->userRolesBeforeUpdate = $this->getAdherentRoles($adherent);
    }

    public function onAdherentProfileAfterUpdate(AdministratorActionEvent $event): void
    {
        if (!$adherent = $event->adherent) {
            return;
        }

        $userRolesAfterUpdate = $this->getAdherentRoles($adherent);

        foreach ($userRolesAfterUpdate as $roleCode => $roleZones) {
            if (!\array_key_exists($roleCode, $this->userRolesBeforeUpdate)) {
                $this->bus->dispatch(
                    new UserRoleHistoryCommand(
                        $adherent->getUuid(),
                        UserRoleHistory::ACTION_ADD,
                        $roleCode,
                        $roleZones,
                        $event->administrator->getId()
                    )
                );
            }
        }

        foreach ($this->userRolesBeforeUpdate as $roleCode => $roleZones) {
            if (!\array_key_exists($roleCode, $userRolesAfterUpdate)) {
                $this->bus->dispatch(
                    new UserRoleHistoryCommand(
                        $adherent->getUuid(),
                        UserRoleHistory::ACTION_REMOVE,
                        $roleCode,
                        $roleZones,
                        $event->administrator->getId()
                    )
                );
            }
        }
    }

    private function getAdherentRoles(Adherent $adherent): array
    {
        $scopes = $this->generalScopeGenerator->generateScopes($adherent);

        $roles = [];
        foreach ($scopes as $scope) {
            $roles[$scope->getMainCode()] = $scope->getZoneNames();
        }

        return $roles;
    }
}
