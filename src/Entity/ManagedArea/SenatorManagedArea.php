<?php

namespace AppBundle\Entity\ManagedArea;

use AppBundle\Entity\ReferentTag;
use AppBundle\Entity\Territory\Department;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="managed_area_senator")
 * @ORM\Entity
 */
class SenatorManagedArea extends ManagedArea
{
    use ManagedDepartment;

    public function __construct(Department $department = null)
    {
        $this->department = $department;
    }

    public function isValid(): bool
    {
        return null !== $this->department;
    }
}
