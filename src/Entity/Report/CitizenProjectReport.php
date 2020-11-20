<?php

namespace App\Entity\Report;

use App\Entity\CitizenProject;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class CitizenProjectReport extends Report
{
    /**
     * @var CitizenProject
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\CitizenProject")
     * @ORM\JoinColumn(name="citizen_project_id")
     */
    protected $subject;
}
