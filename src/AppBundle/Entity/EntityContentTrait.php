<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

trait EntityContentTrait
{
    /**
     * @var string|null
     *
     * @ORM\Column(length=100)
     *
     * @Assert\NotBlank
     */
    private $title;

    /**
     * @var string|null
     *
     * @ORM\Column(length=100, unique=true)
     *
     * @Assert\NotBlank
     */
    private $slug;

    /**
     * @var string|null
     *
     * @ORM\Column(length=255)
     *
     * @Assert\Length(min=10, max=255)
     * @Assert\NotBlank
     */
    private $description;

    /**
     * @var Media|null
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Media")
     *
     * @Assert\NotBlank
     */
    private $media;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text")
     *
     * @Assert\NotBlank
     */
    private $content;

    public function __toString()
    {
        return $this->title;
    }

    /**
     * @return null|string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param null|string $title
     *
     * @return EntityContentTrait
     */
    public function setTitle($title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @param null|string $slug
     *
     * @return EntityContentTrait
     */
    public function setSlug($slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param null|string $description
     *
     * @return EntityContentTrait
     */
    public function setDescription($description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Media|null
     */
    public function getMedia()
    {
        return $this->media;
    }

    /**
     * @param Media|null $media
     *
     * @return EntityContentTrait
     */
    public function setMedia(Media $media = null): self
    {
        $this->media = $media;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param null|string $content
     *
     * @return EntityContentTrait
     */
    public function setContent($content): self
    {
        $this->content = $content;

        return $this;
    }
}
