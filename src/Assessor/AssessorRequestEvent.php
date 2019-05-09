<?php

namespace AppBundle\Assessor;

use AppBundle\Entity\AssessorRequest;
use Symfony\Component\EventDispatcher\Event;

class AssessorRequestEvent extends Event
{
    private $assessorRequest;

    public function __construct(AssessorRequest $assessorRequest)
    {
        $this->assessorRequest = $assessorRequest;
    }

    public function getAssessorRequest(): AssessorRequest
    {
        return $this->assessorRequest;
    }
}
