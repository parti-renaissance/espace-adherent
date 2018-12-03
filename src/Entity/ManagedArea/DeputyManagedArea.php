<?php

namespace AppBundle\Entity\ManagedArea;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\District;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class DeputyManagedArea extends ManagedArea
{
    use ManagedDistrict;

    public function __construct(Adherent $adherent = null, District $district = null)
    {
        parent::__construct($adherent);

        $this->district = $district;
    }
}
