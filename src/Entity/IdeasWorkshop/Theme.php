<?php

namespace AppBundle\Entity\IdeasWorkshop;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use AppBundle\Entity\EntityNameSlugTrait;
use AppBundle\Entity\ImageTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="iw_theme",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="theme_name_unique", columns="name"),
 *         @ORM\UniqueConstraint(name="theme_slug_unique", columns="slug")
 *     }
 * )
 *
 * @UniqueEntity("name")
 * @UniqueEntity("slug")
 *
 * @Algolia\Index(autoIndex=false)
 */
class Theme
{
    use EntityNameSlugTrait;
    use ImageTrait;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $enabled;

    public function __construct(string $name, bool $enabled = false)
    {
        $this->setName($name);
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
}
