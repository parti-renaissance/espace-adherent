<?php

namespace AppBundle\Entity\Report;

use AppBundle\Entity\CitizenAction;
use Doctrine\ORM\Mapping as ORM;
use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;

/**
 * @ORM\Entity
 *
 * @Algolia\Index(autoIndex=false)
 */
class CitizenActionReport extends Report
{
    /**
     * @var CitizenAction
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\CitizenAction")
     * @ORM\JoinColumn(name="citizen_action_id")
     */
    protected $subject;
}
