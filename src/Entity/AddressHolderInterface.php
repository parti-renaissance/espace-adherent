<?php

namespace App\Entity;

use App\Address\AddressInterface;

interface AddressHolderInterface
{
    public function getPostAddress(): ?AddressInterface;
}
