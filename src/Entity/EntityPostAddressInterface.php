<?php

namespace AppBundle\Entity;

interface EntityPostAddressInterface
{
    public function getCountry(): ?string;

    public function getPostalCode(): ?string;
}
