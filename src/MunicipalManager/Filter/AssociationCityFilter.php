<?php

namespace AppBundle\MunicipalManager\Filter;

use AppBundle\Entity\ReferentTag;

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
    private $country;

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
     * @var ReferentTag[]
     */
    private $tags = [];

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

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): void
    {
        $this->country = $country;
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

    public function getTags(): array
    {
        return $this->tags;
    }

    public function setTags(array $tags): void
    {
        $this->tags = $tags;
    }
}
