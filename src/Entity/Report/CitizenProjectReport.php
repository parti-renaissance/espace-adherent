<?php

namespace AppBundle\Entity\Report;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use AppBundle\Entity\CitizenProject;
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
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\CitizenProject")
     * @ORM\JoinColumn(name="citizen_project_id")
     */
    protected $subject;
}
