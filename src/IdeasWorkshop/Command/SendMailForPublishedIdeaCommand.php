<?php

namespace AppBundle\IdeasWorkshop\Command;

use AppBundle\Entity\IdeasWorkshop\Idea;

class SendMailForPublishedIdeaCommand
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
