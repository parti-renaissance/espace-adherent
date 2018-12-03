<?php

namespace AppBundle\Entity\ManagedArea;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use AppBundle\Entity\Adherent;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="managed_areas")
 * @ORM\Entity
 *
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({
 *     "deputy":                "AppBundle\Entity\ManagedArea\DeputyManagedArea",
 *     "communication_manager": "AppBundle\Entity\ManagedArea\CommunicationManagerManagedArea",
 *     "elected_officer":       "AppBundle\Entity\ManagedArea\ElectedOfficerManagedArea",
 *     "referent":              "AppBundle\Entity\ManagedArea\ReferentManagedArea",
 *     "senator":               "AppBundle\Entity\ManagedArea\SenatorManagedArea",
 * })
 *
 * @Algolia\Index(autoIndex=false)
 */
abstract class ManagedArea
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var Adherent|null
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Adherent", inversedBy="managedAreas")
     * @ORM\JoinColumn(nullable=false)
     *
     * @Assert\NotNull
     */
    private $adherent;

    public function __construct(Adherent $adherent = null)
    {
        $this->adherent = $adherent;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAdherent(): ?Adherent
    {
        return $this->adherent;
    }

    public function setAdherent(Adherent $adherent): void
    {
        $this->adherent = $adherent;
    }
}
