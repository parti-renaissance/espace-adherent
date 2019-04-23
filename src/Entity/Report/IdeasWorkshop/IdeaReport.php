<?php

namespace AppBundle\Entity\Report\IdeasWorkshop;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use AppBundle\Entity\IdeasWorkshop\Idea;
use AppBundle\Entity\Report\Report;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 *
 * @Algolia\Index(autoIndex=false)
 */
class IdeaReport extends Report
{
    /**
     * @var Idea
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\IdeasWorkshop\Idea")
     * @ORM\JoinColumn(name="idea_id", onDelete="CASCADE")
     */
    protected $subject;
}
