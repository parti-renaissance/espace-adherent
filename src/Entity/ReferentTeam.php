<?php

namespace AppBundle\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="referent_team")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ProcurationManagerRepository")
 *
 * @Algolia\Index(autoIndex=false)
 */
class ReferentTeam
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var Adherent
     *
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Adherent", inversedBy="referentTeam")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $adherent;

    /**
     * @var Adherent
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Adherent")
     * @ORM\JoinColumn(name="referent_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $referent;

    public function __construct(Adherent $adherent, Adherent $referent)
    {
        $this->adherent = $adherent;
        $this->referent = $referent;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getAdherent(): ?Adherent
    {
        return $this->adherent;
    }

    public function setAdherent(Adherent $adherent = null): void
    {
        $this->adherent = $adherent;
    }

    public function getReferent(): ?Adherent
    {
        return $this->referent;
    }

    public function setReferent(Adherent $referent): void
    {
        $this->referent = $referent;
    }
}
