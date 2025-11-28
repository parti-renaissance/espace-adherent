<?php

declare(strict_types=1);

namespace App\Entity;

use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Interface for manageable image aware entities.
 */
interface ImageManageableInterface extends ImageAwareInterface
{
    public function setImageName(?UploadedFile $image): void;

    public function getImage(): ?UploadedFile;

    public function setImage(?UploadedFile $image): void;

    public function isRemoveImage(): bool;

    public function setRemoveImage(bool $value): void;
}
