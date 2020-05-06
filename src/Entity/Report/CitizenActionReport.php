<?php

namespace App\Entity\Report;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use App\Entity\CitizenAction;
use Doctrine\ORM\Mapping as ORM;

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
     * @ORM\ManyToOne(targetEntity="App\Entity\CitizenAction")
     * @ORM\JoinColumn(name="citizen_action_id")
     */
    protected $subject;
}
