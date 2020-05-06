<?php

namespace App\MunicipalManager\Filter;

use App\Entity\ReferentTag;

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
    private $municipalManagerFirstName;

    /**
     * @var string|null
     */
    private $municipalManagerLastName;

    /**
     * @var string|null
     */
    private $municipalManagerEmail;

    /**
     * @var string|null
     */
    private $managedInseeCode;

    /**
     * @var ReferentTag[]
     */
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

    public function getMunicipalManagerFirstName(): ?string
    {
        return $this->municipalManagerFirstName;
    }

    public function setMunicipalManagerFirstName(?string $municipalManagerFirstName): void
    {
        $this->municipalManagerFirstName = $municipalManagerFirstName;
    }

    public function getMunicipalManagerLastName(): ?string
    {
        return $this->municipalManagerLastName;
    }

    public function setMunicipalManagerLastName(?string $municipalManagerLastName): void
    {
        $this->municipalManagerLastName = $municipalManagerLastName;
    }

    public function getMunicipalManagerEmail(): ?string
    {
        return $this->municipalManagerEmail;
    }

    public function setMunicipalManagerEmail(?string $municipalManagerEmail): void
    {
        $this->municipalManagerEmail = $municipalManagerEmail;
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
