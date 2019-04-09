<?php

namespace AppBundle\Entity\ManagedArea;

use AppBundle\Entity\Territory\Department;
use Doctrine\ORM\Mapping as ORM;

trait ManagedDepartment
{
    /**
     * @var Department
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Territory\Department")
     */
    protected $department;

    public function getDepartment(): ?Department
    {
        return $this->department;
    }

    public function setDepartment(Department $department = null): void
    {
        $this->department = $department;
    }
}
