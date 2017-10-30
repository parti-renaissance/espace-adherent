<?php

namespace AppBundle\Exception;

use Ramsey\Uuid\UuidInterface;

class GroupAlreadyApprovedException extends BaseGroupException
{
    public function __construct(UuidInterface $groupUuid, \Exception $previous = null)
    {
        $message = sprintf('Group %s has already been approved by an administrator.', $groupUuid->toString());

        parent::__construct($groupUuid, $message, $previous);
    }
}
