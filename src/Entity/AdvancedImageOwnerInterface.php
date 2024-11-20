<?php

namespace App\Entity;

interface AdvancedImageOwnerInterface extends ImageOwnerInterface
{
    public function getImageSize(): ?int;

    public function getImageMimeType(): ?string;

    public function getImageWidth(): ?int;

    public function getImageHeight(): ?int;
}
