<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\File\UploadedFile;

trait ImageTrait
{
    /**
     * @var UploadedFile|null
     */
    protected $image;

    #[ORM\Column(nullable: true)]
    protected $imageName;

    private $removeImage = false;

    public function setImageName(?UploadedFile $image): void
    {
        $this->imageName = null === $image ? null :
            \sprintf('%s.%s',
                md5(
                    \sprintf(
                        '%s@%s',
                        method_exists($this, 'getUuid') ? $this->getUuid()->toString() : ($this->getId() ?? Uuid::uuid4()),
                        $image->getClientOriginalName()
                    )
                ),
                $image->getClientOriginalExtension()
            );
    }

    public function getImageName(): ?string
    {
        return $this->imageName;
    }

    public function hasImageName(): bool
    {
        return null !== $this->imageName;
    }

    public function getImage(): ?UploadedFile
    {
        return $this->image;
    }

    public function setImage(?UploadedFile $image): void
    {
        $this->image = $image;
    }

    public function getImagePath(): string
    {
        return $this->getImageName();
    }

    public function isRemoveImage(): bool
    {
        return $this->removeImage;
    }

    public function setRemoveImage(bool $value): void
    {
        $this->removeImage = $value;
    }
}
