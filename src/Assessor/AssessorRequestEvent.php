<?php

namespace App\Assessor;

use App\Entity\AssessorRequest;
use Symfony\Contracts\EventDispatcher\Event;

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
