<?php

namespace App\History\Listener;

use App\Entity\Adherent;
use App\Entity\Reporting\UserRoleHistory;
use App\History\AdministratorActionEvent;
use App\History\AdministratorActionEvents;
use App\Scope\GeneralScopeGenerator;
use App\Scope\Scope;
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

        foreach ($userRolesAfterUpdate as $userRole) {
            if (!\in_array($userRole, $this->userRolesBeforeUpdate, true)) {
                $this->bus->dispatch(
                    new UserRoleHistory(
                        $adherent,
                        UserRoleHistory::ACTION_ADD,
                        $userRole,
                        $event->administrator
                    )
                );
            }
        }

        foreach ($this->userRolesBeforeUpdate as $userRole) {
            if (!\in_array($userRole, $userRolesAfterUpdate, true)) {
                $this->bus->dispatch(
                    new UserRoleHistory(
                        $adherent,
                        UserRoleHistory::ACTION_REMOVE,
                        $userRole,
                        $event->administrator
                    )
                );
            }
        }
    }

    private function getAdherentRoles(Adherent $adherent): array
    {
        $scopes = $this->generalScopeGenerator->generateScopes($adherent);

        return array_map(function (Scope $scope): string {
            return $scope->getCode();
        }, $scopes);
    }
}
