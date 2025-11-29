<?php

declare(strict_types=1);

namespace App\Entity\Mooc;

use App\Entity\Image;
use App\Validator\ImageObject as AssertImageObject;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class MoocImageElement extends BaseMoocElement
{
    /**
     * @var Image|null
     */
    #[AssertImageObject(
        maxSize: '1M',
        mimeTypes: ['image/jpeg', 'image/png'],
        maxWidth: 960,
        maxHeight: 720
    )]
    #[ORM\OneToOne(targetEntity: Image::class, cascade: ['all'], orphanRemoval: true)]
    protected $image;

    public function getImage(): ?Image
    {
        return $this->image;
    }

    public function setImage(?Image $image): void
    {
        $this->image = $image;
    }

    public function getType(): string
    {
        return MoocElementTypeEnum::IMAGE;
    }
}
