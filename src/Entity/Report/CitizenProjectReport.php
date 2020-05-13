<?php

namespace App\Entity\Report;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use App\Entity\CitizenProject;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 *
 * @Algolia\Index(autoIndex=false)
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
