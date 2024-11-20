<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;

trait AdvancedImageTrait
{
    use ImageTrait {
        setImageName as public setBaseImageName;
    }

    #[ORM\Column(type: 'bigint', nullable: true)]
    protected $imageSize;

    #[ORM\Column(length: 50, nullable: true)]
    protected $imageMimeType;

    #[ORM\Column(type: 'integer', nullable: true)]
    protected ?int $imageWidth = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    protected ?int $imageHeight = null;

    public function setImageName(?UploadedFile $image): void
    {
        $this->setBaseImageName($image);

        if (!$image) {
            $this->imageSize = null;
            $this->imageMimeType = null;
            $this->imageWidth = null;
            $this->imageHeight = null;

            return;
        }

        $this->imageSize = (int) $image->getSize();

        if ($infos = getimagesize($image->getPathname())) {
            $this->imageWidth = $infos[0];
            $this->imageHeight = $infos[1];
            $this->imageMimeType = $infos['mime'] ?? null;
        }
    }

    public function getImageSize(): ?int
    {
        return $this->imageSize;
    }

    public function getImageMimeType(): ?string
    {
        return $this->imageMimeType;
    }

    public function getImageWidth(): ?int
    {
        return $this->imageWidth;
    }

    public function getImageHeight(): ?int
    {
        return $this->imageHeight;
    }
}
