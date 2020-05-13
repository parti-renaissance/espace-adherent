<?php

namespace App\Twig;

use App\Entity\IdeasWorkshop\Idea;
use App\Entity\IdeasWorkshop\Thread;
use App\Entity\IdeasWorkshop\ThreadComment;
use App\Entity\Report\ReportableInterface;

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
