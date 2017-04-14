<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="legislative")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\LegislativeRepository")
 */
class Legislative
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="area", type="string", length=255)
     */
    private $area;

    /**
     * @var Adherent
     *
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Adherent", inversedBy="legislative")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $candidate;

    public function getId(): int
    {
        return $this->id;
    }

    public function setArea(string $area): void
    {
        $this->area = $area;
    }

    public function getArea(): ?string
    {
        return $this->area;
    }

    public function getCandidate(): ?Adherent
    {
        return $this->candidate;
    }

    public function setCandidate(?Adherent $candidate): void
    {
        $this->candidate = $candidate;
    }
}

