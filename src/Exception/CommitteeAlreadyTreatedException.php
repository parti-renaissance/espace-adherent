<?php

namespace AppBundle\Exception;

use Ramsey\Uuid\UuidInterface;

class CommitteeAlreadyTreatedException extends CommitteeException
{
    public function __construct(UuidInterface $committeeUuid, \Exception $previous = null)
    {
        $message = sprintf('Committee %s has already been treated by an administrator.', $committeeUuid->toString());

        parent::__construct($committeeUuid, $message, $previous);
    }
}
