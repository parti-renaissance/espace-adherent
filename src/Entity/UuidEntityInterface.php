<?php

declare(strict_types=1);

namespace App\Entity;

use Ramsey\Uuid\UuidInterface;

interface UuidEntityInterface
{
    public function getUuid(): UuidInterface;
}
