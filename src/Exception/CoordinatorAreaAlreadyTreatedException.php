<?php

namespace AppBundle\Exception;

use Ramsey\Uuid\UuidInterface;

class CoordinatorAreaAlreadyTreatedException extends BaseGroupException
{
    public function __construct(UuidInterface $uuid, \Exception $previous = null)
    {
        $message = sprintf('Committee %s has already been treated by an administrator.', $uuid->toString());

        parent::__construct($uuid, $message, $previous);
    }
}
