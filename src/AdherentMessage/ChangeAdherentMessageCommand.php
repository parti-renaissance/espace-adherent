<?php

namespace AppBundle\AdherentMessage;

use AppBundle\Messenger\Message\AsyncMessageInterface;
use Ramsey\Uuid\UuidInterface;

class ChangeAdherentMessageCommand implements AsyncMessageInterface
{
    private $uuid;

    public function __construct(UuidInterface $uuid)
    {
        $this->uuid = $uuid;
    }

    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }
}
