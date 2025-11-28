<?php

declare(strict_types=1);

namespace App\PublicId;

interface PublicIdGeneratorInterface
{
    public function generate(): string;
}
