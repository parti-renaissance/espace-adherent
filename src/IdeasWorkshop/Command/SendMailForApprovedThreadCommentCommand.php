<?php

namespace AppBundle\IdeasWorkshop\Command;

use AppBundle\Entity\IdeasWorkshop\BaseComment;

class SendMailForApprovedThreadCommentCommand
{
    private $comment;

    public function __construct(BaseComment $commend)
    {
        $this->comment = $commend;
    }

    public function getComment(): BaseComment
    {
        return $this->comment;
    }
}
