<?php

namespace AppBundle\Entity\Mooc;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use AppBundle\Entity\Image;
use AppBundle\Validator\ImageObject as AssertImageObject;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 *
 * @Algolia\Index(autoIndex=false)
 */
class MoocImageElement extends BaseMoocElement
{
    /**
     * @var Image|null
     *
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Image", cascade={"all"}, orphanRemoval=true)
     *
     * @AssertImageObject(
     *     mimeTypes={"image/jpeg", "image/png"},
     *     maxSize="1M",
     *     maxWidth="960",
     *     maxHeight="720"
     * )
     */
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
