<?php

declare(strict_types=1);

namespace App\FranceCities;

class CityValueObject
{
    private ?string $name = null;
    private ?string $inseeCode = null;
    private ?array $postalCode = null;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getInseeCode(): ?string
    {
        return $this->inseeCode;
    }

    public function getPostalCode(): ?array
    {
        return $this->postalCode;
    }

    public function getPostalCodeAsString(): string
    {
        return implode(', ', $this->getPostalCode());
    }

    public static function createFromCityArray(array $city): self
    {
        $object = new self();

        $object->name = $city['name'] ?? null;
        $object->inseeCode = $city['insee_code'] ?? null;
        $object->postalCode = $city['postal_code'] ?? null;

        return $object;
    }
}
