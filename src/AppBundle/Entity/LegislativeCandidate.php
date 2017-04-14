<?php

namespace AppBundle\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="legislative_candidates")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\LegislativeRepository")
 *
 * @Algolia\Index(autoIndex=false)
 */
class LegislativeCandidate
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
     * @ORM\Column(nullable=true)
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

    public function setArea(?string $area): void
    {
        $this->area = $area;
    }

    public function getArea(): ?string
    {
        return $this->area;
    }

    public function hasArea(): bool
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
