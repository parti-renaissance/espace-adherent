<?php

namespace AppBundle\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="coordinator_managed_areas")
 * @ORM\Entity
 *
 * @Algolia\Index(autoIndex=false)
 */
class CoordinatorManagedArea
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Adherent", inversedBy="coordinatorManagedAreas")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $adherent;

    /**
     * The codes of the managed zones.
     *
     * @var array
     *
     * @ORM\Column(type="simple_array", nullable=true)
     */
    private $codes = [];

    /**
     * @var string
     *
     * @ORM\Column(nullable=true)
     */
    private $sector;

    public function __construct(array $codes = [], string $sector = '')
    {
        $this->codes = $codes;
        $this->sector = $sector;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAdherent(): ?Adherent
    {
        return $this->adherent;
    }

    public function setAdherent(?Adherent $adherent): void
    {
        $this->adherent = $adherent;
    }

    public function getCodes(): array
    {
        return $this->codes;
    }

    public function setCodes(array $codes): void
    {
        $this->codes = $codes;
    }

    public function getCodesAsString(): string
    {
        return implode(', ', $this->codes);
    }

    public function setCodesAsString(?string $codes): void
    {
        $this->codes = $codes ? array_map('trim', explode(',', $codes)) : [];
    }

    public function getSector(): ?string
    {
        return $this->sector;
    }

    public function setSector(?string $sector): void
    {
        $this->sector = $sector;
    }
}
