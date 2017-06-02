<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

trait EntityMediaTrait
{
    /**
     * @var Media|null
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Media", cascade={"persist"})
     *
     * @Assert\NotBlank
     */
    private $media;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $displayMedia = true;

    public function getMedia(): ?Media
    {
        return $this->media;
    }

    public function setMedia(Media $media = null)
    {
        $this->media = $media;
    }

    public function displayMedia(): bool
    {
        return $this->displayMedia;
    }

    public function setDisplayMedia(bool $displayMedia)
    {
        $this->displayMedia = $displayMedia;
    }
}
