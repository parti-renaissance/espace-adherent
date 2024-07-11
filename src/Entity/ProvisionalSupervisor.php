<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'committee_provisional_supervisor')]
class ProvisionalSupervisor
{
    use EntityTimestampableTrait;

    /**
     * @var int|null
     */
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    private $id;

    /**
     * @var Adherent
     */
    #[ORM\ManyToOne(targetEntity: Adherent::class, inversedBy: 'provisionalSupervisors')]
    private $adherent;

    /**
     * @var Committee
     */
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Committee::class, inversedBy: 'provisionalSupervisors')]
    private $committee;

    public function __construct(Adherent $adherent, Committee $committee)
    {
        $this->adherent = $adherent;
        $this->committee = $committee;
    }

    public function getAdherent(): Adherent
    {
        return $this->adherent;
    }

    public function setAdherent(Adherent $adherent): void
    {
        $this->adherent = $adherent;
    }

    public function getCommittee(): Committee
    {
        return $this->committee;
    }

    public function setCommittee(Committee $committee): void
    {
        $this->committee = $committee;
    }
}
