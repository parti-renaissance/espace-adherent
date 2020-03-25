<?php

namespace AppBundle\Adherent\Command;

use AppBundle\Messenger\Message\AsynchronousMessageInterface;
use Ramsey\Uuid\UuidInterface;

class RemoveAdherentAndRelatedDataCommand implements AsynchronousMessageInterface
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
