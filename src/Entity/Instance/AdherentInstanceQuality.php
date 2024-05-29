<?php

namespace App\Entity\Instance;

use App\Entity\Adherent;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\Entity\Geo\Zone;
use App\Entity\TerritorialCouncil\TerritorialCouncil;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

#[ORM\Table]
#[ORM\UniqueConstraint(name: 'adherent_instance_quality_unique', columns: ['adherent_id', 'instance_quality_id'])]
#[ORM\Entity]
class AdherentInstanceQuality
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;

    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Adherent::class, inversedBy: 'instanceQualities')]
    private $adherent;

    /**
     * @var InstanceQuality
     */
    #[ORM\JoinColumn(nullable: false)]
    #[ORM\ManyToOne(targetEntity: InstanceQuality::class, fetch: 'EAGER')]
    private $instanceQuality;

    /**
     * @var \DateTime
     */
    #[ORM\Column(type: 'datetime')]
    private $date;

    /**
     * @var Zone|null
     */
    #[ORM\ManyToOne(targetEntity: Zone::class)]
    private $zone;

    /**
     * @var TerritorialCouncil|null
     */
    #[ORM\ManyToOne(targetEntity: TerritorialCouncil::class)]
    private $territorialCouncil;

    public function __construct(
        ?Adherent $adherent,
        InstanceQuality $instanceQuality,
        ?\DateTime $date = null,
        ?UuidInterface $uuid = null
    ) {
        $this->adherent = $adherent;
        $this->instanceQuality = $instanceQuality;
        $this->date = $date ?? new \DateTime();
        $this->uuid = $uuid ?? Uuid::uuid4();
    }

    public function getInstanceQuality(): InstanceQuality
    {
        return $this->instanceQuality;
    }

    public function getAdherent(): Adherent
    {
        return $this->adherent;
    }

    public function setTerritorialCouncil(?TerritorialCouncil $territorialCouncil): void
    {
        $this->territorialCouncil = $territorialCouncil;
    }

    public function setZone(?Zone $zone): void
    {
        $this->zone = $zone;
    }

    public function hasNationalCouncilScope(): bool
    {
        return $this->instanceQuality->hasNationalCouncilScope();
    }

    public function setAdherent(Adherent $adherent): void
    {
        $this->adherent = $adherent;
    }
}
