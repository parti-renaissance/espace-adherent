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
        $roles = $this->buildDirectRoles($adherent);

        foreach ($this->delegatedAccessRepository->findBy(['delegated' => $adherent]) as $access) {
            $roles[] = $this->buildDelegatedRole($access);
        }

        return $roles;
    }

    private function buildDirectRoles(Adherent $adherent): array
    {
        $roles = [];

        // Geo-based roles from ZoneBasedRoles
        foreach ($adherent->getZoneBasedRoles() as $role) {
            [$names, $codes, $labels] = $this->buildGeoZoneInfo($role->getZones()->toArray());
            $roles[] = $this->formatRole($role->getType(), false, null, $names, $codes, $labels);
        }

        // ANIMATOR role (from committees relationship, not ZoneBasedRole)
        [$names, $codes, $labels] = $this->getCommitteeZoneInfo($adherent);
        if ($names) {
            $roles[] = $this->formatRole(ScopeEnum::ANIMATOR, false, null, $names, $codes, $labels);
        }

        // AGORA_PRESIDENT role (from agora relationship, not ZoneBasedRole)
        [$names, $codes, $labels] = $this->getAgoraZoneInfo($adherent->presidentOfAgoras->toArray());
        if ($names) {
            $roles[] = $this->formatRole(ScopeEnum::AGORA_PRESIDENT, false, null, $names, $codes, $labels);
        }

        // AGORA_GENERAL_SECRETARY role (from agora relationship, not ZoneBasedRole)
        [$names, $codes, $labels] = $this->getAgoraZoneInfo($adherent->generalSecretaryOfAgoras->toArray());
        if ($names) {
            $roles[] = $this->formatRole(ScopeEnum::AGORA_GENERAL_SECRETARY, false, null, $names, $codes, $labels);
        }

        return $roles;
    }

    private function buildDelegatedRole(DelegatedAccess $access): array
    {
        $type = $access->getType();
        $delegator = $access->getDelegator();

        [$names, $codes, $labels] = $this->getDelegatedZoneInfo($type, $delegator);

        $delegatorData = null;
        if ($delegator) {
            $delegatorData = [
                'first_name' => $delegator->getFirstName(),
                'last_name' => $delegator->getLastName(),
                'gender' => $delegator->getGender(),
            ];
        }

        return $this->formatRole($type, true, $access->getRole(), $names, $codes, $labels, $delegatorData);
    }

    private function getDelegatedZoneInfo(string $type, ?Adherent $delegator): array
    {
        if (!$delegator) {
            return ['', '', ''];
        }

        return match ($type) {
            ScopeEnum::ANIMATOR => $this->getCommitteeZoneInfo($delegator),
            ScopeEnum::AGORA_PRESIDENT => $this->getAgoraZoneInfo($delegator->presidentOfAgoras->toArray()),
            ScopeEnum::AGORA_GENERAL_SECRETARY => $this->getAgoraZoneInfo($delegator->generalSecretaryOfAgoras->toArray()),
            default => $this->buildGeoZoneInfo($this->getZonesFromDelegator($type, $delegator)),
        };
    }

    private function getCommitteeZoneInfo(Adherent $adherent): array
    {
        $committees = array_filter(
            $adherent->getAnimatorCommittees(),
            fn (Committee $c) => $c->isApproved()
        );

        if (empty($committees)) {
            return ['', '', ''];
        }

        $names = array_map(fn (Committee $c) => $c->getName(), $committees);
        $codes = array_map(fn (Committee $c) => $c->getUuid()->toString(), $committees);
        sort($names);
        sort($codes);

        $namesStr = implode(', ', $names);

        // Labels = names for committees
        return [$namesStr, implode(', ', $codes), $namesStr];
    }

    /**
     * @param Agora[] $agoras
     */
    private function getAgoraZoneInfo(array $agoras): array
    {
        $agoras = array_filter($agoras, fn (Agora $a) => $a->published);

        if (empty($agoras)) {
            return ['', '', ''];
        }

        $names = array_map(fn (Agora $a) => $a->getName(), $agoras);
        $codes = array_map(fn (Agora $a) => $a->getUuid()->toString(), $agoras);
        sort($names);
        sort($codes);

        $namesStr = implode(', ', $names);

        // Labels = names for agoras
        return [$namesStr, implode(', ', $codes), $namesStr];
    }

    /**
     * @param Zone[] $zones
     */
    private function buildGeoZoneInfo(array $zones): array
    {
        if (empty($zones)) {
            return ['', '', ''];
        }

        $names = array_map(fn (Zone $zone) => $zone->getName(), $zones);
        $codes = array_map(fn (Zone $zone) => $zone->getCode(), $zones);
        // Labels: region names, other zone codes
        $labels = array_map(
            fn (Zone $zone) => Zone::REGION === $zone->getType() ? $zone->getName() : $zone->getCode(),
            $zones
        );

        sort($names);
        sort($codes);
        sort($labels);

        return [
            implode(', ', array_unique($names)),
            implode(', ', array_unique($codes)),
            implode(', ', array_unique($labels)),
        ];
    }

    private function formatRole(string $type, bool $isDelegated, ?string $function, string $names, string $codes, string $labels, ?array $delegator = null): array
    {
        return [
            'code' => $type,
            'is_delegated' => $isDelegated,
            'function' => $function,
            'zones' => $names ?: null,
            'zone_codes' => $codes ?: null,
            'zone_labels' => $labels ?: null,
            'delegator' => $delegator,
        ];
    }

    /**
     * @return Zone[]
     */
    private function getZonesFromDelegator(string $type, Adherent $delegator): array
    {
        foreach ($delegator->getZoneBasedRoles() as $role) {
            if ($role->getType() === $type) {
                return $role->getZones()->toArray();
            }
        }

        return [];
    }
}
