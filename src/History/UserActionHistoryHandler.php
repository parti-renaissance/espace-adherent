<?php

namespace App\History;

use App\Entity\Adherent;
use App\Entity\Administrator;
use App\Entity\Agora;
use App\Entity\Committee;
use App\Entity\Event\Event;
use App\Entity\Geo\Zone;
use App\Entity\MyTeam\DelegatedAccess;
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

    public function createDelegatedAccessAdd(DelegatedAccess $delegatedAccess): void
    {
        $this->addDelegatedAccessHistory(UserActionHistoryTypeEnum::DELEGATED_ACCESS_ADD, $delegatedAccess);
    }

    public function createDelegatedAccessEdit(DelegatedAccess $delegatedAccess): void
    {
        $this->addDelegatedAccessHistory(UserActionHistoryTypeEnum::DELEGATED_ACCESS_EDIT, $delegatedAccess);
    }

    public function createDelegatedAccessRemove(DelegatedAccess $delegatedAccess): void
    {
        $this->addDelegatedAccessHistory(UserActionHistoryTypeEnum::DELEGATED_ACCESS_REMOVE, $delegatedAccess);
    }

    private function addDelegatedAccessHistory(UserActionHistoryTypeEnum $type, DelegatedAccess $delegatedAccess): void
    {
        $delegator = $delegatedAccess->getDelegator();
        $zoneBasedRole = $delegator->findZoneBasedRole($delegatedAccess->getType());

        $data = [
            'delegator_uuid' => $delegator->getUuid()->toString(),
            'scope' => $delegatedAccess->getType(),
            'features' => $delegatedAccess->getScopeFeatures(),
            'role' => $delegatedAccess->getRole(),
            'zones' => $zoneBasedRole ? $this->getZoneNames($zoneBasedRole->getZones()->toArray()) : null,
        ];

        $author = $this->getCurrentUser();

        if ($author && !$author->equals($delegator)) {
            $data['author_uuid'] = $author->getUuid()->toString();
        }

        $this->dispatch($delegatedAccess->getDelegated(), $type, $data);
    }

    public function createAgoraMembershipAdd(Adherent $adherent, Agora $agora, ?Administrator $administrator = null): void
    {
        $this->createAgoraHistory(UserActionHistoryTypeEnum::AGORA_MEMBERSHIP_ADD, $adherent, $agora, $administrator);
    }

    public function createAgoraMembershipRemove(Adherent $adherent, Agora $agora, ?Administrator $administrator = null): void
    {
        $this->createAgoraHistory(UserActionHistoryTypeEnum::AGORA_MEMBERSHIP_REMOVE, $adherent, $agora, $administrator);
    }

    public function createAgoraPresidentAdd(Adherent $adherent, Agora $agora, ?Administrator $administrator = null): void
    {
        $this->createAgoraHistory(UserActionHistoryTypeEnum::AGORA_PRESIDENT_ADD, $adherent, $agora, $administrator);
    }

    public function createAgoraPresidentRemove(Adherent $adherent, Agora $agora, ?Administrator $administrator = null): void
    {
        $this->createAgoraHistory(UserActionHistoryTypeEnum::AGORA_PRESIDENT_REMOVE, $adherent, $agora, $administrator);
    }

    public function createAgoraGeneralSecretaryAdd(Adherent $adherent, Agora $agora, ?Administrator $administrator = null): void
    {
        $this->createAgoraHistory(UserActionHistoryTypeEnum::AGORA_GENERAL_SECRETARY_ADD, $adherent, $agora, $administrator);
    }

    public function createAgoraGeneralSecretaryRemove(Adherent $adherent, Agora $agora, ?Administrator $administrator = null): void
    {
        $this->createAgoraHistory(UserActionHistoryTypeEnum::AGORA_GENERAL_SECRETARY_REMOVE, $adherent, $agora, $administrator);
    }

    private function createAgoraHistory(
        UserActionHistoryTypeEnum $type,
        Adherent $adherent,
        Agora $agora,
        ?Administrator $administrator = null,
    ): void {
        $this->dispatch(
            $adherent,
            $type,
            [
                'agora' => $agora->getName(),
                'agora_id' => $agora->getId(),
            ],
            $administrator
        );
    }

    public function createMembershipAnniversaryReminded(Adherent $adherent): void
    {
        $this->dispatch($adherent, UserActionHistoryTypeEnum::MEMBERSHIP_ANNIVERSARY_REMINDED);
    }

    public function createCommitteeCreate(Adherent $adherent, Committee $committee): void
    {
        $this->dispatch(
            $adherent,
            UserActionHistoryTypeEnum::COMMITTEE_CREATE,
            [
                'committee_id' => $committee->getId(),
                'name' => $committee->getName(),
            ]
        );
    }

    public function createCommitteeUpdate(Adherent $adherent, Committee $committee, array $before, array $after): void
    {
        $this->dispatch(
            $adherent,
            UserActionHistoryTypeEnum::COMMITTEE_UPDATE,
            [
                'committee_id' => $committee->getId(),
                'before' => $before,
                'after' => $after,
            ]
        );
    }

    public function createCommitteeDelete(Adherent $adherent, Committee $committee): void
    {
        $this->dispatch(
            $adherent,
            UserActionHistoryTypeEnum::COMMITTEE_DELETE,
            [
                'committee_id' => $committee->getId(),
                'name' => $committee->getName(),
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

    private function getCurrentUser(): ?Adherent
    {
        $user = $this->security->getUser();

        return $user instanceof Adherent ? $user : null;
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
