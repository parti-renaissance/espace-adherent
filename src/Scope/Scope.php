<?php

declare(strict_types=1);

namespace App\Scope;

use App\Entity\Adherent;
use App\Entity\Geo\Zone;
use Symfony\Component\Serializer\Attribute\Groups;

class Scope
{
    #[Groups(['scopes', 'scope'])]
    private string $code;

    #[Groups(['scopes', 'scope'])]
    private string $name;

    #[Groups(['scopes', 'scope'])]
    private string $roleName;

    private ?string $mainRoleName;

    #[Groups(['scopes', 'scope'])]
    private array $zones;

    #[Groups(['scopes', 'scope'])]
    private array $apps;

    #[Groups(['scopes', 'scope'])]
    private array $features;

    #[Groups(['scope'])]
    private ?DelegatedAccess $delegatedAccess;

    #[Groups(['scopes', 'scope'])]
    private ?array $attributes = null;

    private ?Adherent $currentUser;

    public function __construct(
        string $code,
        string $name,
        string $roleName,
        array $zones,
        array $apps,
        array $features,
        Adherent $adherent,
        ?DelegatedAccess $delegatedAccess = null,
        ?string $mainRoleName = null,
    ) {
        $this->code = $code;
        $this->name = $name;
        $this->roleName = $roleName;
        $this->mainRoleName = $mainRoleName;
        $this->zones = $zones;
        $this->apps = $apps;
        $this->features = $features;
        $this->currentUser = $adherent;
        $this->delegatedAccess = $delegatedAccess;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return Zone[]
     */
    public function getZones(): array
    {
        return $this->zones;
    }

    public function getApps(): array
    {
        return $this->apps;
    }

    public function getFeatures(): array
    {
        return $this->features;
    }

    public function getDelegatedAccess(): ?DelegatedAccess
    {
        return $this->delegatedAccess;
    }

    public function hasFeature(string $featureCode): bool
    {
        return \in_array($featureCode, $this->features, true);
    }

    public function containsFeatures(array $featureCodes): bool
    {
        return 0 < \count(array_intersect($featureCodes, $this->features));
    }

    public function isNational(): bool
    {
        return ScopeEnum::isNational($this->getMainCode());
    }

    public function getMainUser(): ?Adherent
    {
        return $this->getDelegator() ?? $this->currentUser;
    }

    public function getCurrentUser(): ?Adherent
    {
        return $this->currentUser;
    }

    public function getMainCode(): ?string
    {
        return $this->getDelegatorCode() ?? $this->getCode();
    }

    public function getDelegatorCode(): ?string
    {
        return $this->delegatedAccess?->getType();
    }

    public function getDelegator(): ?Adherent
    {
        return $this->delegatedAccess?->getDelegator();
    }

    public function getAttributes(): ?array
    {
        return $this->attributes;
    }

    public function getAttribute(string $key, mixed $default = null)
    {
        return $this->attributes[$key] ?? $default;
    }

    public function addAttribute(string $name, $value): void
    {
        $this->attributes[$name] = $value;
    }

    public function getCommitteeUuids(): array
    {
        return array_column($this->attributes['committees'] ?? [], 'uuid');
    }

    public function getAgoraUuids(): array
    {
        return array_column($this->attributes['agoras'] ?? [], 'uuid');
    }

    public function getMainRoleName(): string
    {
        return $this->mainRoleName ?? $this->roleName;
    }

    public function getRoleName(): string
    {
        return $this->roleName;
    }

    public function getScopeInstance(): ?string
    {
        return ScopeEnum::SCOPE_INSTANCES[$this->getMainCode()] ?? null;
    }

    public function getZoneNames(): array
    {
        if (ScopeEnum::ANIMATOR === $this->getMainCode()) {
            $zones = array_column($this->attributes['committees'] ?? [], 'name');
        } elseif (\in_array($this->getMainCode(), [ScopeEnum::AGORA_PRESIDENT, ScopeEnum::AGORA_GENERAL_SECRETARY])) {
            $zones = array_column($this->attributes['agoras'] ?? [], 'name');
        } else {
            $zones = array_map(fn (Zone $zone) => match ($zone->getType()) {
                Zone::DISTRICT => $zone->getName().' ('.$zone->getCode().')',
                default => $zone->getName(),
            }, $this->zones);
        }

        return $zones;
    }

    public function getAutomaticallyDelegatableFeatures(): array
    {
        return array_intersect([FeatureEnum::FEATUREBASE], $this->features);
    }

    public function getInstanceKey(): string
    {
        $parts = [$this->getMainCode()];

        if ($zones = $this->getZones()) {
            $parts[] = $zones[0]->getCode();
        } elseif ($committees = $this->getCommitteeUuids()) {
            $parts[] = $committees[0];
        } elseif ($agoras = $this->getAgoraUuids()) {
            $parts[] = $agoras[0];
        }

        return implode(':', $parts);
    }
}
