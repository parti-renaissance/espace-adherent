<?php

namespace App\IdeasWorkshop\Command;

use App\Entity\IdeasWorkshop\BaseComment;

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
