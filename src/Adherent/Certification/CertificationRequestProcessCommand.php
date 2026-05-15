<?php

declare(strict_types=1);

namespace App\Adherent\Certification;

use App\Messenger\Message\AsynchronousMessageInterface;
use Symfony\Component\Uid\Uuid;

class CertificationRequestProcessCommand implements AsynchronousMessageInterface
{
    private $uuid;

    public function __construct(Uuid $uuid)
    {
        $this->uuid = $uuid;
    }

    public function getUuid(): Uuid
    {
        return $this->uuid;
    }
}
