<?php

namespace AppBundle\Entity\ManagedArea;

use AppBundle\Entity\District;

class DeputyManagedArea extends ManagedArea
{
    use ManagedDistrict;

    public function __construct(District $district = null)
    {
        $this->district = $district;
    }

    public function isValid(): bool
    {
        return null !== $this->district;
    }
}
