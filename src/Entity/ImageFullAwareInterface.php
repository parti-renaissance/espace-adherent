<?php

namespace App\Entity;

/**
 * Interface for full image (with metadata) aware entities.
 */
interface ImageFullAwareInterface extends ImageAwareInterface, ImageMetadataProviderInterface
{
}
