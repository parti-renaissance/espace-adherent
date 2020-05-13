<?php

namespace App\Exception;

use Ramsey\Uuid\UuidInterface;

class CitizenProjectAlreadyApprovedException extends BaseGroupException
{
    public function __construct(UuidInterface $citizenProjectUuid, \Exception $previous = null)
    {
        $message = sprintf('Citizen Project %s has already been approved by an administrator.', $citizenProjectUuid->toString());

        parent::__construct($citizenProjectUuid, $message, $previous);
    }
}
