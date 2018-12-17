<?php

namespace AppBundle\Entity\Report\IdeasWorkshop;

use AppBundle\Entity\IdeasWorkshop\Thread;
use AppBundle\Entity\Report\Report;
use Doctrine\ORM\Mapping as ORM;
use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;

/**
 * @ORM\Entity
 *
 * @Algolia\Index(autoIndex=false)
 */
class ThreadReport extends Report
{
    /**
     * @var Thread
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\IdeasWorkshop\Thread")
     * @ORM\JoinColumn(name="thread_id")
     */
    protected $subject;
}
