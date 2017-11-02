<?php

namespace AppBundle\Exception;

use Ramsey\Uuid\UuidInterface;

class GroupAlreadyApprovedException extends BaseGroupException
{
    public function __construct(UuidInterface $committeeUuid, \Exception $previous = null)
    {
        $message = sprintf('Group %s has already been approved by an administrator.', $committeeUuid->toString());

        parent::__construct($committeeUuid, $message, $previous);
    }
}
