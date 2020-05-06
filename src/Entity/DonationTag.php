<?php

namespace App\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="donation_tags",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="donation_tag_label_unique", columns="label")
 *     }
 * )
 *
 * @UniqueEntity("label")
 *
 * @Algolia\Index(autoIndex=false)
 */
class DonationTag
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
     */
    private $label;

    /**
     * @ORM\Column
     *
     * @Assert\NotBlank
     */
    private $color;

    public function __construct(?string $label = null, ?string $color = null)
    {
        $this->label = $label;
        $this->color = $color;
    }

    public function __toString(): string
    {
        return (string) $this->label;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): void
    {
        $this->color = $color;
    }
}
