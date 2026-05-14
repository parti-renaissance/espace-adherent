<?php

declare(strict_types=1);

namespace App\AdherentMessage\Command;

use App\Mailchimp\SynchronizeMessageInterface;
use Symfony\Component\Uid\Uuid;

class CreateStaticSegmentCommand implements SynchronizeMessageInterface
{
    private $uuid;
    private $entityClass;

    public function __construct(Uuid $uuid, string $entityClass)
    {
        $this->uuid = $uuid;
        $this->entityClass = $entityClass;
    }

    public function getUuid(): Uuid
    {
        return $this->uuid;
    }

    public function getEntityClass(): string
    {
        return $this->entityClass;
    }
}
