<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
class DonatorKinship
{
    /**
     * The unique auto incremented primary key.
     */
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Id]
    private $id;

    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Donator::class, inversedBy: 'kinships')]
    private $donator;

    #[Assert\NotBlank(message: 'Veuillez spécifier un Donateur à associer.')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Donator::class)]
    private $related;

    #[Assert\Length(min: 2, max: 100)]
    #[Assert\NotBlank(message: 'Veuillez spécifier un lien de parenté.')]
    #[ORM\Column(length: 100, nullable: false)]
    private $kinship;

    public function __construct(?Donator $donator = null, ?Donator $related = null, ?string $kinship = null)
    {
        $this->donator = $donator;
        $this->related = $related;
        $this->kinship = $kinship;
    }

    public function __toString(): string
    {
        return \sprintf(
            '%s (%s)',
            $this->related,
            $this->kinship
        );
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDonator(): ?Donator
    {
        return $this->donator;
    }

    public function setDonator(?Donator $donator): void
    {
        $this->donator = $donator;
    }

    public function getRelated(): ?Donator
    {
        return $this->related;
    }

    public function setRelated(?Donator $related): void
    {
        $this->related = $related;
    }

    public function getKinship(): ?string
    {
        return $this->kinship;
    }

    public function setKinship(string $kinship): void
    {
        $this->kinship = $kinship;
    }
}
