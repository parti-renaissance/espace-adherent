<?php

namespace AppBundle\Entity\ManagedArea;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="managed_area_european_deputy")
 * @ORM\Entity
 */
class EuropeanDeputyManagedArea extends ManagedArea
{
    /**
     * @var bool
     */
    private $isEuropeanDeputy = false;

    public function isEuropeanDeputy(): bool
    {
        return $this->isEuropeanDeputy;
    }

    public function setIsEuropeanDeputy(bool $isEuropeanDeputy): void
    {
        $this->isEuropeanDeputy = $isEuropeanDeputy;
    }

    public function isValid(): bool
    {
        return $this->isEuropeanDeputy();
    }
}
