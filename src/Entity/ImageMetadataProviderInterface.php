<?php

declare(strict_types=1);

namespace App\Entity;

/**
 * Interface for image metadata aware entities.
 */
interface ImageMetadataProviderInterface
{
    public function getImageSize(): ?int;

    public function getImageMimeType(): ?string;

    public function getImageWidth(): ?int;

    public function getImageHeight(): ?int;
}
