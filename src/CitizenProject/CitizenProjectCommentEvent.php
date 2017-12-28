<?php

namespace AppBundle\CitizenProject;

use AppBundle\Entity\CitizenProject;
use AppBundle\Entity\CitizenProjectComment;

class CitizenProjectCommentEvent extends CitizenProjectEvent
{
    private $comment;
    private $sendMail;

    public function __construct(CitizenProject $citizenProject, CitizenProjectComment $comment, ?bool $sendMail)
    {
        parent::__construct($citizenProject);

        $this->comment = $comment;
        $this->sendMail = $sendMail;
    }

    public function getComment(): CitizenProjectComment
    {
        return $this->comment;
    }

    public function isSendMail(): ?bool
    {
        return $this->sendMail;
    }
}
