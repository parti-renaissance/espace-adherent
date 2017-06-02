<?php

namespace AppBundle\Exception;

use Ramsey\Uuid\UuidInterface;

class CommitteeAlreadyApprovedException extends CommitteeException
{
    public function __construct(UuidInterface $committeeUuid, \Exception $previous = null)
    {
        $message = sprintf('Committee %s has already been approved by an administrator.', $committeeUuid->toString());

        parent::__construct($committeeUuid, $message, $previous);
    }
}
