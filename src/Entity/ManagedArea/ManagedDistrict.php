<?php

namespace AppBundle\Entity\ManagedArea;

use AppBundle\Entity\District;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

trait ManagedDistrict
{
    /**
     * @var District|null
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\District")
     *
     * @Assert\NotNull
     */
    private $district;

    public function getDistrict(): ?District
    {
        return $this->district;
    }

    public function setDistrict(District $district): void
    {
        $this->district = $district;
    }

    public function __toString(): string
    {
        return (string) $this->district;
    }
}
