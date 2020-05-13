<?php

namespace App\Entity\Report\IdeasWorkshop;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use App\Entity\IdeasWorkshop\Thread;
use App\Entity\Report\Report;
use Doctrine\ORM\Mapping as ORM;

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
     * @ORM\ManyToOne(targetEntity="App\Entity\IdeasWorkshop\Thread")
     * @ORM\JoinColumn(name="thread_id", onDelete="CASCADE")
     */
    protected $subject;
}
