<?php

namespace App\Exception;

use App\Entity\CitizenProject;
use Throwable;

class CitizenProjectException extends \RuntimeException
{
    private $citizenProject;

    public function __construct(CitizenProject $citizenProject, $message = '', $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->citizenProject = $citizenProject;
    }

    public function getCitizenProject(): CitizenProject
    {
        return $this->citizenProject;
    }
}
