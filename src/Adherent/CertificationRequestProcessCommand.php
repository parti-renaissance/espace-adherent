<?php

namespace App\Adherent;

use App\Mailchimp\SynchronizeMessageInterface;
use Ramsey\Uuid\UuidInterface;

class CertificationRequestProcessCommand implements SynchronizeMessageInterface
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
