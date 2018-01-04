<?php

namespace AppBundle\Entity\Timeline;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="timeline_profiles")
 * @ORM\Entity
 */
class Profile
{
    /**
     * @var int
     *
     * @ORM\Column(type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string|null
     *
     * @ORM\Column(length=100)
     *
     * @Assert\Length(max=100)
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
     * @ORM\Column(type="text")
     *
     * @Assert\NotBlank
     *
     * @Algolia\Attribute
     */
    private $description;

    public function __construct(string $title, string $slug, string $description)
    {
        $this->title = $title;
        $this->slug = $slug;
        $this->description = $description;
    }

    public function __toString()
    {
        return $this->title;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): void
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
}
