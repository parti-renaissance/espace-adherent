<?php

declare(strict_types=1);

namespace App\History\Listener;

use App\Entity\Adherent;
use App\History\AdministratorActionEvent;
use App\History\AdministratorActionEvents;
use App\History\AdministratorActionHistoryHandler;
use App\History\UserActionHistoryHandler;
use App\Scope\GeneralScopeGenerator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UserRoleHistorySubscriber implements EventSubscriberInterface
{
    private array $userRolesBeforeUpdate = [];

    public function __construct(
        private readonly UserActionHistoryHandler $userActionHistoryHandler,
        private readonly AdministratorActionHistoryHandler $administratorActionHistoryHandler,
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

        $administrator = $event->administrator;

        $userRolesAfterUpdate = $this->getAdherentRoles($adherent);

        foreach ($userRolesAfterUpdate as $role => $zones) {
            if (!\array_key_exists($role, $this->userRolesBeforeUpdate)) {
                $this->userActionHistoryHandler->createRoleAdd(
                    $adherent,
                    $role,
                    $zones,
                    $administrator
                );

                $this->administratorActionHistoryHandler->createAdherentRoleAdd(
                    $administrator,
                    $adherent,
                    $role,
                    $zones
                );
            }
        }

        foreach ($this->userRolesBeforeUpdate as $role => $zones) {
            if (!\array_key_exists($role, $userRolesAfterUpdate)) {
                $this->userActionHistoryHandler->createRoleRemove(
                    $adherent,
                    $role,
                    $zones,
                    $administrator
                );

                $this->administratorActionHistoryHandler->createAdherentRoleRemove(
                    $administrator,
                    $adherent,
                    $role,
                    $zones
                );
            }
        }
    }

    private function getAdherentRoles(Adherent $adherent): array
    {
        $scopes = $this->generalScopeGenerator->generateScopes($adherent, false);

        $roles = [];
        foreach ($scopes as $scope) {
            $roles[$scope->getMainCode()] = $scope->getZones();
        }

        return $roles;
    }
}
