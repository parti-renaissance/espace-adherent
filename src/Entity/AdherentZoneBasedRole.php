<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Adherent\Authorization\ZoneBasedRoleTypeEnum;
use App\Collection\ZoneCollection;
use App\Entity\Geo\Zone;
use App\Repository\AdherentZoneBasedRoleRepository;
use App\Scope\ScopeEnum;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/v3/zone_based_role',
        ),
        new Post(
            uriTemplate: '/v3/zone_based_role',
        ),
        new Put(
            uriTemplate: '/v3/zone_based_role/{uuid}',
            requirements: ['uuid' => '%pattern_uuid%']
        ),
        new Delete(
            uriTemplate: '/v3/zone_based_role/{uuid}',
            requirements: ['uuid' => '%pattern_uuid%']
        ),
    ],
    normalizationContext: ['groups' => ['zone_based_role_read']],
    denormalizationContext: ['groups' => ['zone_based_role_write']],
    validationContext: ['groups' => ['Default', 'zone_based_role_write']],
    security: "is_granted('REQUEST_SCOPE_GRANTED', 'circonscriptions')"
)]
#[ORM\Entity(repositoryClass: AdherentZoneBasedRoleRepository::class)]
class AdherentZoneBasedRole
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use EntityZoneTrait;

    #[Assert\Choice(choices: ZoneBasedRoleTypeEnum::ALL)]
    #[Assert\NotBlank]
    #[Groups(['zone_based_role_read', 'zone_based_role_write', 'profile_update'])]
    #[ORM\Column]
    private ?string $type;

    #[Assert\NotBlank]
    #[Groups(['zone_based_role_read', 'zone_based_role_write'])]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Adherent::class, inversedBy: 'zoneBasedRoles')]
    private ?Adherent $adherent = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $hidden = false;

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

    public static function createProcurationManager(array $zones): self
    {
        return static::create(ScopeEnum::PROCURATIONS_MANAGER, $zones);
    }

    public static function createMunicipalPilot(array $zones): self
    {
        return static::create(ScopeEnum::MUNICIPAL_PILOT, $zones);
    }

    public static function createFdeCoordinator(array $zones): self
    {
        return static::create(ScopeEnum::FDE_COORDINATOR, $zones);
    }

    public static function createNational(string $nationalScope, Zone $zone): self
    {
        return static::create($nationalScope, [$zone]);
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

    public function getAdherent(): ?Adherent
    {
        return $this->adherent;
    }

    public function setAdherent(Adherent $adherent): void
    {
        $this->adherent = $adherent;
    }

    public function setHidden(bool $hidden): void
    {
        $this->hidden = $hidden;
    }

    public function isHidden(): bool
    {
        return $this->hidden;
    }
}
