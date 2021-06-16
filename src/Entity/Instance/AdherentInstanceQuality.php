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

/**
 * @ORM\Entity
 * @ORM\Table(uniqueConstraints={
 *     @ORM\UniqueConstraint(name="adherent_instance_quality_unique", columns={"adherent_id", "instance_quality_id"}),
 * })
 */
class AdherentInstanceQuality
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Adherent", inversedBy="instanceQualities")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE", unique=true)
     */
    private $adherent;

    /**
     * @var InstanceQuality
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Instance\InstanceQuality")
     * @ORM\JoinColumn(nullable=false)
     */
    private $instanceQuality;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @var Zone|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Geo\Zone")
     */
    private $zone;

    /**
     * @var TerritorialCouncil|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\TerritorialCouncil\TerritorialCouncil")
     */
    private $territorialCouncil;

    public function __construct(
        Adherent $adherent,
        InstanceQuality $instanceQuality,
        \DateTime $date,
        UuidInterface $uuid = null
    ) {
        $this->adherent = $adherent;
        $this->instanceQuality = $instanceQuality;
        $this->date = $date;
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
}
