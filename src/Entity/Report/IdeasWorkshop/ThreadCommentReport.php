<?php

namespace AppBundle\Entity\Report\IdeasWorkshop;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use AppBundle\Entity\IdeasWorkshop\ThreadComment;
use AppBundle\Entity\Report\Report;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 *
 * @Algolia\Index(autoIndex=false)
 */
class ThreadCommentReport extends Report
{
    /**
     * @var ThreadComment
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\IdeasWorkshop\ThreadComment")
     * @ORM\JoinColumn(name="thread_comment_id", onDelete="CASCADE")
     */
    protected $subject;
}
