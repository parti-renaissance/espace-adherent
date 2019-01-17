<?php

namespace AppBundle\Twig;

use AppBundle\Entity\IdeasWorkshop\Idea;
use AppBundle\Entity\IdeasWorkshop\Thread;
use AppBundle\Entity\IdeasWorkshop\ThreadComment;
use AppBundle\Entity\Report\ReportableInterface;

class IdeasWorkshopRuntime
{
    public function isIdea(ReportableInterface $idea): string
    {
        return $idea instanceof Idea;
    }

    public function isThread(ReportableInterface $comment): string
    {
        return $comment instanceof Thread;
    }

    public function isThreadComment(ReportableInterface $comment): string
    {
        return $comment instanceof ThreadComment;
    }
}
