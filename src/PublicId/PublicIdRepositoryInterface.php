<?php

namespace App\PublicId;

interface PublicIdRepositoryInterface
{
    public function publicIdExists(string $publicId): bool;
}
