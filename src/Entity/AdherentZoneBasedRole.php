<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 */
class AdherentZoneBasedRole
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use EntityZoneTrait;

    /**
     * @ORM\Column
     *
     * @Assert\NotBlank
     * @Assert\Choice(choices=App\Adherent\Authorization\ZoneBasedRoleTypeEnum::ALL)
     */
    private ?string $type;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Adherent", inversedBy="zoneBasedRoles")
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=false)]
     *
     * @Assert\NotBlank
     */
    private ?Adherent $adherent = null;

    public function __construct(string $type = null)
    {
        $this->uuid = Uuid::uuid4();
        $this->type = $type;
        $this->zones = new ArrayCollection();
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): void
    {
        $this->type = $type;
    }

    public function setAdherent(Adherent $adherent): void
    {
        $this->adherent = $adherent;
    }
}
