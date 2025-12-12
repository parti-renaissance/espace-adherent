<?php

declare(strict_types=1);

namespace App\Adherent\Command;

use App\Entity\Adherent;
use App\Messenger\Message\UuidDefaultAsyncMessage;
use Ramsey\Uuid\UuidInterface;

class UpdateAdherentLinkCommand extends UuidDefaultAsyncMessage
{
    public function __construct(UuidInterface $uuid, public readonly string $resourceClass = Adherent::class)
    {
        parent::__construct($uuid);
    }
}
