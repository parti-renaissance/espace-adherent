<?php

declare(strict_types=1);

namespace App\Adherent\Command;

use App\Entity\Adherent;
use App\Messenger\Message\UuidDefaultAsyncMessage;
use Symfony\Component\Uid\Uuid;

class UpdateAdherentLinkCommand extends UuidDefaultAsyncMessage
{
    public function __construct(Uuid $uuid, public readonly string $resourceClass = Adherent::class)
    {
        parent::__construct($uuid);
    }
}
