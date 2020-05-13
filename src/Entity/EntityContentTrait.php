<?php

namespace App\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
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
     * @Assert\Length(max=100)
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
     * @Assert\Length(max=100)
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
     * @ORM\Column(length=255, nullable=true)
     *
     * @Assert\Length(min=10, max=255)
     */
    private $twitterDescription;

    /**
     * @var string|null
     *
     * @ORM\Column(length=255, nullable=true)
     *
     * @Assert\Length(max=255)
     *
     * @Algolia\Attribute
     */
    private $keywords;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text")
     *
     * @Assert\NotBlank
     */
    private $content;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $ampContent;

    public function __toString(): string
    {
        return $this->title ?: '';
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): void
    {
        $this->slug = $slug;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getTwitterDescription(): ?string
    {
        return $this->twitterDescription;
    }

    public function setTwitterDescription(?string $twitterDescription): void
    {
        $this->twitterDescription = $twitterDescription;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): void
    {
        $this->content = $content;
    }

    public function getAmpContent(): ?string
    {
        return $this->ampContent;
    }

    public function setAmpContent(?string $ampContent): void
    {
        $this->ampContent = $ampContent;
    }

    public function getKeywords(): ?string
    {
        return $this->keywords;
    }

    public function setKeywords(?string $keywords): void
    {
        $this->keywords = $keywords;
    }
}
