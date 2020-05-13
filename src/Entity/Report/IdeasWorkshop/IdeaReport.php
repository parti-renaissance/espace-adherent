<?php

namespace App\Entity\Report\IdeasWorkshop;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use App\Entity\IdeasWorkshop\Idea;
use App\Entity\Report\Report;
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
     * @ORM\ManyToOne(targetEntity="App\Entity\IdeasWorkshop\Idea")
     * @ORM\JoinColumn(name="idea_id", onDelete="CASCADE")
     */
    protected $subject;
}
