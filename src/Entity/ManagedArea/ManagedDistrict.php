<?php

namespace AppBundle\Entity\ManagedArea;

use AppBundle\Entity\District;
use Doctrine\ORM\Mapping as ORM;

trait ManagedDistrict
{
    /**
     * @var District|null
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\District")
     */
    protected $district;

    public function getDistrict(): ?District
    {
        return $this->district;
    }

    public function setDistrict(District $district = null): void
    {
        $this->district = $district;
    }
}
