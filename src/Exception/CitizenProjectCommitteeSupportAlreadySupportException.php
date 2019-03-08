<?php

namespace AppBundle\Exception;

use AppBundle\Entity\CitizenProject;
use AppBundle\Entity\Committee;
use Throwable;

class CitizenProjectCommitteeSupportAlreadySupportException extends CitizenProjectCommitteeSupportException
{
    private $committee;
    private $citizenProject;

    public function __construct(
        Committee $committee,
        CitizenProject $citizenProject,
        $message = '',
        $code = 0,
        Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->committee = $committee;
        $this->citizenProject = $citizenProject;
    }

    public function getCommittee(): Committee
    {
        return $this->committee;
    }

    public function getCitizenProject(): CitizenProject
    {
        return $this->citizenProject;
    }
}
