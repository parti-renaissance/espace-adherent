<?php

namespace AppBundle\Entity\IdeasWorkshop;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use AppBundle\Entity\EntityNameSlugTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="iw_need",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="need_name_unique", columns="name"),
 *         @ORM\UniqueConstraint(name="need_slug_unique", columns="slug")
 *     }
 * )
 *
 * @UniqueEntity("name")
 * @UniqueEntity("slug")
 *
 * @Algolia\Index(autoIndex=false)
 */
class Need
{
    use EntityNameSlugTrait;

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
