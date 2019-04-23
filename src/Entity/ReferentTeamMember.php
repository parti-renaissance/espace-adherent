<?php

namespace AppBundle\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 *
 * @Algolia\Index(autoIndex=false)
 */
class ReferentTeamMember
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
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Adherent", inversedBy="referentTeamMember")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE", unique=true)
     */
    private $member;

    /**
     * @var Adherent
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Adherent")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $referent;

    public function __construct(Adherent $referent)
    {
        $this->referent = $referent;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getMember(): Adherent
    {
        return $this->member;
    }

    public function getReferent(): Adherent
    {
        return $this->referent;
    }

    public function setMember(Adherent $member): void
    {
        $this->member = $member;
    }
}
