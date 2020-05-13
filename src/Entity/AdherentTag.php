<?php

namespace App\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="adherent_tags",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="adherent_tag_name_unique", columns="name")
 *     }
 * )
 *
 * @UniqueEntity("name")
 *
 * @Algolia\Index
 */
class AdherentTag
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned": true})
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @ORM\Column(length=100, unique=true)
     *
     * @Assert\NotBlank
     * @Assert\Length(max="100")
     *
     * @Algolia\Attribute
     */
    private $name = '';

    public function __construct(?string $name = null)
    {
        if ($name) {
            $this->name = $name;
        }
    }

    public function __toString(): string
    {
        return $this->name ?: '';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }
}
