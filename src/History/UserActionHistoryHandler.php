<?php

namespace App\History;

use App\Entity\Adherent;
use App\Entity\Administrator;
use App\Entity\Event\Event;
use App\Entity\Geo\Zone;
use App\History\Command\UserActionHistoryCommand;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Authentication\Token\SwitchUserToken;

class UserActionHistoryHandler
{
    public function __construct(
        private readonly Security $security,
        private readonly MessageBusInterface $bus,
    ) {
    }

    public function createLoginSuccess(Adherent $adherent): void
    {
        $this->dispatch(
            $adherent,
            UserActionHistoryTypeEnum::LOGIN_SUCCESS
        );
    }

    public function createLoginFailure(Adherent $adherent): void
    {
        $this->dispatch(
            $adherent,
            UserActionHistoryTypeEnum::LOGIN_FAILURE
        );
    }

    public function createProfileUpdate(Adherent $adherent, array $properties): void
    {
        $this->dispatch(
            $adherent,
            UserActionHistoryTypeEnum::PROFILE_UPDATE,
            $properties,
            $this->getImpersonator()
        );
    }

    public function createImpersonationStart(Administrator $administrator, Adherent $adherent): void
    {
        $this->dispatch(
            $adherent,
            UserActionHistoryTypeEnum::IMPERSONATION_START,
            null,
            $administrator
        );
    }

    public function createImpersonationEnd(Adherent $adherent, Administrator $administrator): void
    {
        $this->dispatch(
            $adherent,
            UserActionHistoryTypeEnum::IMPERSONATION_END,
            null,
            $administrator
        );
    }

    public function createPasswordResetRequest(Adherent $adherent): void
    {
        $this->dispatch(
            $adherent,
            UserActionHistoryTypeEnum::PASSWORD_RESET_REQUEST
        );
    }

    public function createPasswordResetValidate(Adherent $adherent): void
    {
        $this->dispatch(
            $adherent,
            UserActionHistoryTypeEnum::PASSWORD_RESET_VALIDATE
        );
    }

    public function createEmailChangeRequest(Adherent $adherent): void
    {
        $this->dispatch(
            $adherent,
            UserActionHistoryTypeEnum::EMAIL_CHANGE_REQUEST,
            null,
            $this->getImpersonator()
        );
    }

    public function createEmailChangeValidate(Adherent $adherent): void
    {
        $this->dispatch(
            $adherent,
            UserActionHistoryTypeEnum::EMAIL_CHANGE_VALIDATE,
            null,
            $this->getImpersonator()
        );
    }

    /** @param Zone[] $zones */
    public function createRoleAdd(Adherent $adherent, string $role, array $zones, Administrator $administrator): void
    {
        $this->dispatch(
            $adherent,
            UserActionHistoryTypeEnum::ROLE_ADD,
            [
                'role' => $role,
                'zones' => $this->getZoneNames($zones),
            ],
            $administrator
        );
    }

    /** @param Zone[] $zones */
    public function createRoleRemove(Adherent $adherent, string $role, array $zones, Administrator $administrator): void
    {
        $this->dispatch(
            $adherent,
            UserActionHistoryTypeEnum::ROLE_REMOVE,
            [
                'role' => $role,
                'zones' => $this->getZoneNames($zones),
            ],
            $administrator
        );
    }

    public function createLiveParticipation(Adherent $adherent, Event $event): void
    {
        $this->dispatch(
            $adherent,
            UserActionHistoryTypeEnum::LIVE_VIEW,
            [
                'event' => $event->getName(),
                'event_id' => $event->getId(),
            ]
        );
    }

    private function getImpersonator(): ?Administrator
    {
        $token = $this->security->getToken();

        if (!$token instanceof SwitchUserToken) {
            return null;
        }

        $administrator = $token->getOriginalToken()->getUser();

        return $administrator instanceof Administrator ? $administrator : null;
    }

    private function dispatch(
        Adherent $adherent,
        UserActionHistoryTypeEnum $type,
        ?array $data = null,
        ?Administrator $administrator = null,
    ): void {
        $this->bus->dispatch(
            new UserActionHistoryCommand(
                $adherent->getUuid(),
                $type,
                $data,
                $administrator?->getId()
            )
        );
    }

    /** @param Zone[] $zones */
    private function getZoneNames(array $zones): array
    {
        return array_map(
            function (Zone $zone): string {
                return \sprintf('%s (%s)', $zone->getName(), $zone->getCode());
            },
            $zones
        );
    }
}
