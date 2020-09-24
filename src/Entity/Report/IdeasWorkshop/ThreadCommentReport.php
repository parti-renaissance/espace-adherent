<?php

namespace App\Entity\Report\IdeasWorkshop;

use App\Entity\IdeasWorkshop\ThreadComment;
use App\Entity\Report\Report;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class ThreadCommentReport extends Report
{
    /**
     * @var ThreadComment
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\IdeasWorkshop\ThreadComment")
     * @ORM\JoinColumn(name="thread_comment_id", onDelete="CASCADE")
     */
    protected $subject;
}
