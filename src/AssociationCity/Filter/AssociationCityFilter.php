<?php

namespace App\AssociationCity\Filter;

class AssociationCityFilter
{
    /**
     * @var string|null
     */
    private $name;

    /**
     * @var string|null
     */
    private $inseeCode;

    /**
     * @var string|null
     */
    private $managedInseeCode;

    private $managedTags = [];

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getInseeCode(): ?string
    {
        return $this->inseeCode;
    }

    public function setInseeCode(?string $inseeCode): void
    {
        $this->inseeCode = $inseeCode;
    }

    public function getManagedInseeCode(): ?string
    {
        return $this->managedInseeCode;
    }

    public function setManagedInseeCode(?string $managedInseeCode): void
    {
        $this->managedInseeCode = $managedInseeCode;
    }

    public function getManagedTags(): array
    {
        return $this->managedTags;
    }

    public function setManagedTags(array $managedTags): void
    {
        $this->managedTags = $managedTags;
    }
}
