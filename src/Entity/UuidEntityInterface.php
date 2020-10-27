<?php

namespace App\Entity;

use Ramsey\Uuid\UuidInterface;

interface UuidEntityInterface
{
    public function getUuid(): UuidInterface;
}
