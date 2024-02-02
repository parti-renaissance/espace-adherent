<?php

namespace App\Entity;

use App\Collection\ZoneCollection;
use App\Entity\Geo\Zone;
use App\Scope\ScopeEnum;
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

    public function __construct(?string $type = null)
    {
        $this->uuid = Uuid::uuid4();
        $this->type = $type;
        $this->zones = new ZoneCollection();
    }

    public static function createCorrespondent(Zone $zone): self
    {
        return static::create(ScopeEnum::CORRESPONDENT, [$zone]);
    }

    public static function createLegislativeCandidate(Zone $zone): self
    {
        return static::create(ScopeEnum::LEGISLATIVE_CANDIDATE, [$zone]);
    }

    public static function createDeputy(Zone $zone): self
    {
        return static::create(ScopeEnum::DEPUTY, [$zone]);
    }

    public static function createRegionalCoordinator(array $zones): self
    {
        return static::create(ScopeEnum::REGIONAL_COORDINATOR, $zones);
    }

    public static function createPresidentDepartmentalAssembly(array $zones): self
    {
        return static::create(ScopeEnum::PRESIDENT_DEPARTMENTAL_ASSEMBLY, $zones);
    }

    private static function create(string $scope, array $zones): self
    {
        $role = new self($scope);

        foreach ($zones as $zone) {
            $role->addZone($zone);
        }

        return $role;
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
