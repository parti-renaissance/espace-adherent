<?php

declare(strict_types=1);

namespace App\Entity;

/**
 * Interface for full image (with metadata) aware entities.
 */
interface ImageFullAwareInterface extends ImageAwareInterface, ImageMetadataProviderInterface
{
}
