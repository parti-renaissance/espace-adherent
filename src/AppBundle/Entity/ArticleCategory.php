<?php

namespace AppBundle\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="articles_categories")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ArticleCategoryRepository")
 *
 * @Algolia\Index(autoIndex=false)
 */
class ArticleCategory
{
    const DEFAULT_CATEGORY = 'tout';

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int|null
     *
     * @ORM\Column(type="smallint")
     *
     * @Assert\NotBlank
     */
    private $position;

    /**
     * @var string
     *
     * @ORM\Column(length=50)
     *
     * @Assert\NotBlank
     *
     * @Algolia\Attribute
     */
    private $name;

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

    public function __construct(string $name = '', string $slug = '', int $position = 1)
    {
        $this->name = $name;
        $this->slug = $slug;
        $this->position = $position;
    }

    public function __toString()
    {
        return $this->name ?: '';
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): ArticleCategory
    {
        $this->position = $position;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): ArticleCategory
    {
        $this->name = $name;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): ArticleCategory
    {
        $this->slug = $slug;

        return $this;
    }

    public static function isDefault(string $category): bool
    {
        return $category === self::DEFAULT_CATEGORY;
    }
}
