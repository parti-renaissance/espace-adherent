<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @UniqueEntity("label")
 */
#[ORM\Table(name: 'donation_tags')]
#[ORM\Entity]
class DonationTag
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private $id;

    /**
     * @Assert\NotBlank
     * @Assert\Length(max="100")
     */
    #[ORM\Column(length: 100, unique: true)]
    private $label;

    /**
     * @Assert\NotBlank
     */
    #[ORM\Column]
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
