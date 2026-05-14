<?php

declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\Uid\Uuid;

class BaseGroupException extends \RuntimeException
{
    private $groupUuid;

    public function __construct(Uuid $groupUuid, $message = '', ?\Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);

        $this->groupUuid = $groupUuid;
    }

    public function getCommitteeUuid(): Uuid
    {
        return $this->groupUuid;
    }

    public function getGroupUuid(): Uuid
    {
        return $this->groupUuid;
    }
}
