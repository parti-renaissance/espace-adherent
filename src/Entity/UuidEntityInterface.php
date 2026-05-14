<?php

declare(strict_types=1);

namespace App\Entity;

use Symfony\Component\Uid\Uuid;

interface UuidEntityInterface
{
    public function getUuid(): Uuid;
}
