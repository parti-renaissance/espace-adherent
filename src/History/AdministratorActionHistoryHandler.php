<?php

declare(strict_types=1);

namespace App\History;

use App\Entity\Adherent;
use App\Entity\Administrator;
use App\Entity\Committee;
use App\Entity\Geo\Zone;
use App\History\Command\AdministratorActionHistoryCommand;
use Symfony\Component\Messenger\MessageBusInterface;

class AdministratorActionHistoryHandler
{
    public function __construct(
        private readonly MessageBusInterface $bus,
    ) {
    }

    public function createLoginSuccess(Administrator $administrator): void
    {
        $this->dispatch(
            $administrator,
            AdministratorActionHistoryTypeEnum::LOGIN_SUCCESS
        );
    }

    public function createLoginFailure(Administrator $administrator): void
    {
        $this->dispatch(
            $administrator,
            AdministratorActionHistoryTypeEnum::LOGIN_FAILURE
        );
    }

    public function createImpersonationStart(Administrator $administrator, Adherent $adherent): void
    {
        $this->dispatch(
            $administrator,
            AdministratorActionHistoryTypeEnum::IMPERSONATION_START,
            [
                'adherent_uuid' => $adherent->getUuid()->toString(),
            ]
        );
    }

    public function createImpersonationEnd(Adherent $adherent, Administrator $administrator): void
    {
        $this->dispatch(
            $administrator,
            AdministratorActionHistoryTypeEnum::IMPERSONATION_END,
            [
                'adherent_uuid' => $adherent->getUuid()->toString(),
            ]
        );
    }

    public function createExport(Administrator $administrator, string $route, array $parameters): void
    {
        $this->dispatch(
            $administrator,
            AdministratorActionHistoryTypeEnum::EXPORT,
            [
                'route' => $route,
                'parameters' => $parameters,
            ]
        );
    }

    public function createAdherentProfileUpdate(Administrator $administrator, Adherent $adherent, array $properties): void
    {
        $this->dispatch(
            $administrator,
            AdministratorActionHistoryTypeEnum::ADHERENT_PROFILE_UPDATE,
            [
                'adherent_uuid' => $adherent->getUuid()->toString(),
                'properties' => $properties,
            ]
        );
    }

    /** @param Zone[] $zones */
    public function createAdherentRoleAdd(Administrator $administrator, Adherent $adherent, string $role, array $zones): void
    {
        $this->dispatch(
            $administrator,
            AdministratorActionHistoryTypeEnum::ADHERENT_ROLE_ADD,
            [
                'adherent_uuid' => $adherent->getUuid()->toString(),
                'role' => $role,
                'zones' => $this->getZoneNames($zones),
            ]
        );
    }

    /** @param Zone[] $zones */
    public function createAdherentRoleRemove(Administrator $administrator, Adherent $adherent, string $role, array $zones): void
    {
        $this->dispatch(
            $administrator,
            AdministratorActionHistoryTypeEnum::ADHERENT_ROLE_REMOVE,
            [
                'adherent_uuid' => $adherent->getUuid()->toString(),
                'role' => $role,
                'zones' => $this->getZoneNames($zones),
            ]
        );
    }

    public function createCommitteeUpdate(Administrator $administrator, Committee $committee, array $beforeUpdate, array $afterUpdate): void
    {
        $this->dispatch(
            $administrator,
            AdministratorActionHistoryTypeEnum::COMMITTEE_UPDATE,
            [
                'committee_uuid' => $committee->getUuid()->toString(),
                'before' => $beforeUpdate,
                'after' => $afterUpdate,
            ]
        );
    }

    public function createCommitteeCreate(Administrator $administrator, Committee $committee): void
    {
        $this->dispatch(
            $administrator,
            AdministratorActionHistoryTypeEnum::COMMITTEE_CREATE,
            [
                'committee_uuid' => $committee->getUuid()->toString(),
                'committee_name' => $committee->getName(),
            ]
        );
    }

    public function createCommitteeDelete(Administrator $administrator, Committee $committee): void
    {
        $this->dispatch(
            $administrator,
            AdministratorActionHistoryTypeEnum::COMMITTEE_DELETE,
            [
                'committee_uuid' => $committee->getUuid()->toString(),
                'committee_name' => $committee->getName(),
            ]
        );
    }

    private function dispatch(
        Administrator $administrator,
        AdministratorActionHistoryTypeEnum $type,
        ?array $data = null,
    ): void {
        $this->bus->dispatch(
            new AdministratorActionHistoryCommand(
                $administrator->getId(),
                $type,
                $data
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
