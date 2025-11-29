<?php

declare(strict_types=1);

namespace App\Admin;

interface ImageUploadAdminInterface
{
    public function getUploadableImagePropertyNames(): array;
}
