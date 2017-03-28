<?php

namespace AppBundle\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use AppBundle\Utils\EmojisRemover;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

trait EntityContentTrait
{
    /**
     * @var string|null
     *
     * @ORM\Column(length=100)
     *
     * @Assert\NotBlank
     *
     * @Algolia\Attribute
     */
    private $title;

    /**
     * @var string|null
     *
     * @ORM\Column(length=100, unique=true)
     *
     * @Assert\NotBlank
     *
     * @Algolia\Attribute
     */
    private $slug;

    /**
     * @var string|null
     *
     * @ORM\Column(length=255)
     *
     * @Assert\Length(min=10, max=255)
     * @Assert\NotBlank
     *
     * @Algolia\Attribute
     */
    private $description;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text")
     *
     * @Assert\NotBlank
     *
     * @Algolia\Attribute
     */
    private $content;

    public function __toString()
    {
        return $this->title ?: '';
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
        $this->title = EmojisRemover::remove($title);

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
        $this->description = EmojisRemover::remove($description);

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
        $this->content = EmojisRemover::remove($content);

        return $this;
    }
}
