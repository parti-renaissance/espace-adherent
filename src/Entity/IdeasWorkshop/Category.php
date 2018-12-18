<?php

namespace AppBundle\Entity\IdeasWorkshop;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="ideas_workshop_category",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="category_name_unique", columns="name")
 *     }
 * )
 *
 * @UniqueEntity("name")
 *
 * @Algolia\Index(autoIndex=false)
 */
class Category
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var string
     *
     * @SymfonySerializer\Groups("idea_list_read")
     * @ORM\Column
     */
    protected $name;

    /**
     * @var bool
     *
     * @SymfonySerializer\Groups("idea_list_read")
     * @ORM\Column(type="boolean")
     */
    private $enabled;

    public function __construct(string $name = null, bool $enabled = false)
    {
        $this->name = $name;
        $this->enabled = $enabled;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function __toString(): string
    {
        return $this->name ?: '';
    }
}
