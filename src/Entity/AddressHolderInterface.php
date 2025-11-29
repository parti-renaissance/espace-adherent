<?php

declare(strict_types=1);

namespace App\Entity;

use App\Address\AddressInterface;

interface AddressHolderInterface
{
    public function getPostAddress(): ?AddressInterface;
}
