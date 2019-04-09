<?php

namespace AppBundle\AdherentMessage\Command;

use AppBundle\Messenger\Message\AsyncMessageInterface;
use Ramsey\Uuid\UuidInterface;

class CreateStaticSegmentCommand implements AsyncMessageInterface
{
    private $uuid;
    private $entityClass;

    public function __construct(UuidInterface $uuid, string $entityClass)
    {
        $this->uuid = $uuid;
        $this->entityClass = $entityClass;
    }

    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }

    public function getEntityClass(): string
    {
        return $this->entityClass;
    }
}
