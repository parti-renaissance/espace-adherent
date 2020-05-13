<?php

namespace App\IdeasWorkshop\Command;

use App\Entity\IdeasWorkshop\Idea;

class SendMailForExtendedIdeaCommand
{
    private $idea;

    public function __construct(Idea $idea)
    {
        $this->idea = $idea;
    }

    public function getIdea(): Idea
    {
        return $this->idea;
    }
}
