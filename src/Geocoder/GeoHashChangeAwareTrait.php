<?php

namespace App\Geocoder;

trait GeoHashChangeAwareTrait
{
    private bool $isAddressChanged = false;

    public function markAddressAsChanged(): void
    {
        $this->isAddressChanged = true;
    }

    public function isAddressChanged(): bool
    {
        return $this->isAddressChanged;
    }
}
