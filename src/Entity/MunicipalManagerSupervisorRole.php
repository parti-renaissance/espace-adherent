<?php

namespace AppBundle\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="municipal_manager_supervisor_role")
 * @ORM\Entity
 *
 * @Algolia\Index(autoIndex=false)
 */
class MunicipalManagerSupervisorRole
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

    public function getReferent(): Adherent
    {
        return $this->referent;
    }
}
