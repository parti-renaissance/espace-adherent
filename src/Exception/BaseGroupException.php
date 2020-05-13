<?php

namespace App\Exception;

use Ramsey\Uuid\UuidInterface;

class BaseGroupException extends \RuntimeException
{
    private $groupUuid;

    public function __construct(UuidInterface $groupUuid, $message = '', \Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);

        $this->groupUuid = $groupUuid;
    }

    public function getCommitteeUuid(): UuidInterface
    {
        return $this->groupUuid;
    }

    public function getGroupUuid(): UuidInterface
    {
        return $this->groupUuid;
    }
}
