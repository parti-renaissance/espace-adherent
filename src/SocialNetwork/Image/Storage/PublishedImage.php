<?php

declare(strict_types=1);

namespace App\SocialNetwork\Image\Storage;

/**
 * Result of publishing an image to the public bucket: its public path and, when available, its
 * pixel dimensions (null only when they could not be determined from the image content).
 */
class PublishedImage
{
    public function __construct(
        public readonly string $path,
        public readonly ?int $width = null,
        public readonly ?int $height = null,
    ) {
    }
}
