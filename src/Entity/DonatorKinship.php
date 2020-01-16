<?php

namespace AppBundle\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="donator_kinship")
 * @ORM\Entity
 *
 * @Algolia\Index(autoIndex=false)
 */
class DonatorKinship
{
    /**
     * The unique auto incremented primary key.
     *
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned": true})
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Donator::class, inversedBy="kinships")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $donator;

    /**
     * @Assert\NotBlank(message="Veuillez spécifier un Donateur à associer.")
     *
     * @ORM\ManyToOne(targetEntity=Donator::class)
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $related;

    /**
     * @ORM\Column(length=100, nullable=false)
     *
     * @Assert\NotBlank(message="Veuillez spécifier un lien de parenté.")
     * @Assert\Length(
     *     min=2,
     *     max=100,
     * )
     */
    private $kinship;

    public function __construct(Donator $donator = null, Donator $related = null, string $kinship = null)
    {
        $this->donator = $donator;
        $this->related = $related;
        $this->kinship = $kinship;
    }

    public function __toString(): string
    {
        return sprintf(
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
