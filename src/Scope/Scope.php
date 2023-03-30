<?php

namespace App\Scope;

use App\Entity\Adherent;
use App\Entity\Geo\Zone;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;

class Scope
{
    /**
     * @SymfonySerializer\Groups({"scopes", "scope"})
     */
    private string $code;

    /**
     * @SymfonySerializer\Groups({"scopes", "scope"})
     */
    private string $name;

    /**
     * @SymfonySerializer\Groups({"scopes", "scope"})
     */
    private array $zones;

    /**
     * @SymfonySerializer\Groups({"scopes", "scope"})
     */
    private array $apps;

    /**
     * @SymfonySerializer\Groups({"scope"})
     */
    private array $features;

    /**
     * @SymfonySerializer\Groups({"scope"})
     */
    private ?DelegatedAccess $delegatedAccess;

    /**
     * @SymfonySerializer\Groups({"scope"})
     */
    private ?array $attributes = null;

    public function __construct(
        string $code,
        string $name,
        array $zones,
        array $apps,
        array $features,
        DelegatedAccess $delegatedAccess = null
    ) {
        $this->code = $code;
        $this->name = $name;
        $this->zones = $zones;
        $this->apps = $apps;
        $this->features = $features;
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
        return \in_array($this->getMainCode(), ScopeEnum::NATIONAL_SCOPES, true);
    }

    public function getMainCode(): ?string
    {
        return $this->getDelegatorCode() ?? $this->getCode();
    }

    public function getDelegatorCode(): ?string
    {
        return $this->delegatedAccess ? $this->delegatedAccess->getType() : null;
    }

    public function getDelegator(): ?Adherent
    {
        return $this->delegatedAccess ? $this->delegatedAccess->getDelegator() : null;
    }

    public function getAttributes(): ?array
    {
        return $this->attributes;
    }

    public function addAttribute(string $name, $value): void
    {
        $this->attributes[$name] = $value;
    }
}
