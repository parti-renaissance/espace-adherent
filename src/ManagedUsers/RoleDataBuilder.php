<?php

declare(strict_types=1);

namespace App\ManagedUsers;

use App\Entity\Adherent;
use App\Entity\Agora;
use App\Entity\Committee;
use App\Entity\Geo\Zone;
use App\Entity\MyTeam\DelegatedAccess;
use App\Repository\MyTeam\DelegatedAccessRepository;
use App\Scope\ScopeEnum;

class RoleDataBuilder
{
    public function __construct(private readonly DelegatedAccessRepository $delegatedAccessRepository)
    {
    }

    public function buildRoles(Adherent $adherent): array
    {
        $roles = [];

        foreach ($adherent->getZoneBasedRoles() as $role) {
            $type = $role->getType();
            [$names, $codes] = $this->getZoneInfo($type, $adherent, $role->getZones()->toArray());
            $roles[] = $this->formatRole($type, false, null, $names, $codes);
        }

        foreach ($this->delegatedAccessRepository->findBy(['delegated' => $adherent]) as $access) {
            $type = $access->getType();
            $delegator = $access->getDelegator();
            $zones = $delegator ? $this->getZonesFromDelegator($access) : [];
            [$names, $codes] = $this->getZoneInfo($type, $delegator, $zones);
            $roles[] = $this->formatRole($type, true, $access->getRole(), $names, $codes);
        }

        return $roles;
    }

    public function buildRolesFromEntities(Adherent $adherent, iterable $delegatedAccesses = []): array
    {
        $roles = [];

        foreach ($adherent->getZoneBasedRoles() as $role) {
            $type = $role->getType();
            [$names, $codes] = $this->getZoneInfo($type, $adherent, $role->getZones()->toArray());
            $roles[] = $this->formatRole($type, false, null, $names, $codes);
        }

        foreach ($delegatedAccesses as $access) {
            $type = $access->getType();
            $delegator = $access->getDelegator();
            $zones = $delegator ? $this->getZonesFromDelegator($access) : [];
            [$names, $codes] = $this->getZoneInfo($type, $delegator, $zones);
            $roles[] = $this->formatRole($type, true, $access->getRole(), $names, $codes);
        }

        return $roles;
    }

    /**
     * @param Zone[] $geoZones
     */
    private function getZoneInfo(string $type, ?Adherent $adherent, array $geoZones): array
    {
        if (!$adherent) {
            return ['', ''];
        }

        return match ($type) {
            ScopeEnum::ANIMATOR => $this->getCommitteeZoneInfo($adherent),
            ScopeEnum::AGORA_PRESIDENT => $this->getAgoraZoneInfo($adherent->presidentOfAgoras->toArray()),
            ScopeEnum::AGORA_GENERAL_SECRETARY => $this->getAgoraZoneInfo($adherent->generalSecretaryOfAgoras->toArray()),
            default => $this->buildGeoZoneInfo($geoZones),
        };
    }

    private function getCommitteeZoneInfo(Adherent $adherent): array
    {
        $committees = $adherent->getAnimatorCommittees();
        if (empty($committees)) {
            return ['', ''];
        }

        $names = array_map(fn (Committee $c) => $c->getName(), $committees);
        $codes = array_map(fn (Committee $c) => $c->getUuid()->toString(), $committees);
        sort($names);
        sort($codes);

        return [implode(', ', $names), implode(', ', $codes)];
    }

    /**
     * @param Agora[] $agoras
     */
    private function getAgoraZoneInfo(array $agoras): array
    {
        if (empty($agoras)) {
            return ['', ''];
        }

        $names = array_map(fn (Agora $a) => $a->getName(), $agoras);
        $codes = array_map(fn (Agora $a) => $a->getUuid()->toString(), $agoras);
        sort($names);
        sort($codes);

        return [implode(', ', $names), implode(', ', $codes)];
    }

    /**
     * @param Zone[] $zones
     */
    private function buildGeoZoneInfo(array $zones): array
    {
        if (empty($zones)) {
            return ['', ''];
        }

        $names = array_map(
            fn (Zone $zone) => Zone::REGION === $zone->getType() ? $zone->getName() : $zone->getCode(),
            $zones
        );
        $codes = array_map(fn (Zone $zone) => $zone->getCode(), $zones);
        sort($names);
        sort($codes);

        return [implode(', ', array_unique($names)), implode(', ', array_unique($codes))];
    }

    private function formatRole(string $type, bool $isDelegated, ?string $function, string $names, string $codes): array
    {
        return [
            'code' => $type,
            'is_delegated' => $isDelegated,
            'function' => $function,
            'zones' => $names ?: null,
            'zone_codes' => $codes ?: null,
        ];
    }

    /**
     * @return Zone[]
     */
    private function getZonesFromDelegator(DelegatedAccess $access): array
    {
        $delegator = $access->getDelegator();

        if (!$delegator) {
            return [];
        }

        foreach ($delegator->getZoneBasedRoles() as $role) {
            if ($role->getType() === $access->getType()) {
                return $role->getZones()->toArray();
            }
        }

        return [];
    }
}
