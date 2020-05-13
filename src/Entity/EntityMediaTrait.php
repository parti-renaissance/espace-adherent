<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

trait EntityMediaTrait
{
    /**
     * @var Media|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Media", cascade={"persist"})
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

    public function setMedia(Media $media = null): void
    {
        $this->media = $media;
    }

    public function displayMedia(): bool
    {
        return $this->displayMedia;
    }

    public function setDisplayMedia(bool $displayMedia): void
    {
        $this->displayMedia = $displayMedia;
    }
}
