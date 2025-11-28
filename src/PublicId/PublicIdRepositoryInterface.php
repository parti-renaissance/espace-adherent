<?php

declare(strict_types=1);

namespace App\PublicId;

interface PublicIdRepositoryInterface
{
    public function publicIdExists(string $publicId): bool;
}
