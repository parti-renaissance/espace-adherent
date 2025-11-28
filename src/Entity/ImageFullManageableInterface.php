<?php

declare(strict_types=1);

namespace App\Entity;

/**
 * Interface for manageable image (with metadata) aware entities.
 */
interface ImageFullManageableInterface extends ImageManageableInterface, ImageMetadataProviderInterface
{
}
