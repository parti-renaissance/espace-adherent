<?php

namespace AppBundle\Entity\Report;

use AppBundle\Entity\CitizenProject;
use Doctrine\ORM\Mapping as ORM;
use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;

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
