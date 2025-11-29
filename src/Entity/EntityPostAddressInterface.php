<?php

declare(strict_types=1);

namespace App\Entity;

interface EntityPostAddressInterface
{
    public function getCountry(): ?string;

    public function getPostalCode(): ?string;

    public function getInseeCode(): ?string;
}
