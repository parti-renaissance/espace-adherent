<?php

declare(strict_types=1);

namespace App\Entity\Geo;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Address\AddressInterface;
use App\Entity\EntityTimestampableTrait;
use App\Entity\UuidEntityInterface;
use App\Repository\Geo\ZoneRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiFilter(filterClass: SearchFilter::class, properties: ['name' => 'word_start', 'type' => 'exact'])]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(uriTemplate: '/zones'),
    ],
    normalizationContext: ['groups' => ['zone_read']],
    order: ['name' => 'ASC'],
    paginationClientItemsPerPage: true
)]
#[ORM\AttributeOverrides([new ORM\AttributeOverride(name: 'code', column: new ORM\Column(unique: false))])]
#[ORM\Entity(repositoryClass: ZoneRepository::class)]
#[ORM\Index(columns: ['type'])]
#[ORM\Table(name: 'geo_zone')]
#[ORM\UniqueConstraint(name: 'geo_zone_code_type_unique', columns: ['code', 'type'])]
class Zone implements GeoInterface, UuidEntityInterface
{
    use GeoTrait;
    use EntityTimestampableTrait;

    public const CUSTOM = 'custom';
    public const COUNTRY = 'country';
    public const REGION = 'region';
    public const DEPARTMENT = 'department';
    public const DISTRICT = 'district';
    public const CITY = 'city';
    public const BOROUGH = 'borough';
    public const CITY_COMMUNITY = 'city_community';
    public const CANTON = 'canton';
    public const FOREIGN_DISTRICT = 'foreign_district';
    public const CONSULAR_DISTRICT = 'consular_district';
    public const VOTE_PLACE = 'vote_place';

    public const FDE_CODE = 'FDE';

    public const TYPES = [
        Zone::CUSTOM,
        Zone::FOREIGN_DISTRICT,
        Zone::COUNTRY,
        Zone::CONSULAR_DISTRICT,
        Zone::REGION,
        Zone::DEPARTMENT,
        Zone::CITY,
        Zone::DISTRICT,
        Zone::CITY_COMMUNITY,
        Zone::CANTON,
        Zone::BOROUGH,
        Zone::VOTE_PLACE,
    ];

    /*
     * Committee zone types, the order is important,
     * so it's used on assignation process
     */
    public const COMMITTEE_TYPES = [
        Zone::VOTE_PLACE,
        Zone::BOROUGH,
        Zone::CITY,
        Zone::CITY_COMMUNITY,
        Zone::CANTON,
        Zone::COUNTRY,
        Zone::CUSTOM,
    ];

    public const CANDIDATE_TYPES = [
        self::CANTON,
        self::DEPARTMENT,
        self::REGION,
    ];

    /**
     * The internal primary identity key.
     *
     * @var UuidInterface
     */
    #[ApiProperty(identifier: true, openapiContext: ['type' => 'string', 'format' => 'uuid', 'example' => 'b4219d47-3138-5efd-9762-2ef9f9495084'])]
    #[Groups(['zone_read', 'survey_write_dc', 'scopes', 'scope', 'jecoute_news_read_dc', 'audience_read', 'audience_segment_read', 'survey_list_dc', 'survey_read_dc', 'team_read', 'team_list_read', 'pap_campaign_read', 'pap_campaign_read_after_write', 'phoning_campaign_read', 'phoning_campaign_list', 'department_site_read', 'department_site_read_list', 'elected_representative_read', 'elected_representative_list', 'formation_list_read', 'formation_read', 'formation_write', 'elected_mandate_read', 'adherent_elect_read', 'general_meeting_report_list_read', 'general_meeting_report_read', 'committee:read', 'managed_users_list', 'managed_user_read', 'procuration_request_read', 'procuration_request_list', 'procuration_proxy_list', 'procuration_matched_proxy', 'general_meeting_report_write', 'elected_mandate_write', 'action_read', 'department_site_write', 'committee:write', 'audience_write', 'profile_read', 'zone_based_role_write', 'zone_based_role_read', 'general_convention_read', 'general_convention_write', 'adherent_message_read_filter'])]
    #[ORM\Column(type: 'uuid', unique: true)]
    protected $uuid;

    /**
     * @var string
     */
    #[Groups(['zone_read', 'scope', 'committee:read', 'zone:code,type', 'managed_users_list', 'managed_user_read', 'procuration_request_read', 'procuration_request_list', 'procuration_proxy_list', 'procuration_matched_proxy', 'action_read', 'profile_read', 'zone_based_role_read', 'general_convention_read', 'profile_update', 'adherent_message_read_filter'])]
    #[ORM\Column]
    private $type;

    /**
     * @var ?string
     */
    #[ORM\Column(length: 6, nullable: true)]
    private $teamCode;

    /**
     * @var Collection|self[]
     */
    #[ORM\InverseJoinColumn(name: 'parent_id', referencedColumnName: 'id')]
    #[ORM\JoinColumn(name: 'child_id', referencedColumnName: 'id')]
    #[ORM\JoinTable(name: 'geo_zone_parent')]
    #[ORM\ManyToMany(targetEntity: Zone::class, inversedBy: 'children')]
    private $parents;

    /**
     * @var Collection
     */
    #[ORM\ManyToMany(targetEntity: Zone::class, mappedBy: 'parents')]
    private $children;

    /**
     * @var string[]|null
     */
    #[Groups(['zone_read'])]
    #[ORM\Column(type: 'simple_array', nullable: true)]
    private $postalCode;

    #[ORM\Column(type: 'simple_array', nullable: true)]
    private ?array $tags = null;

    public function __construct(string $type, string $code, string $name, ?UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
        $this->type = $type;
        $this->code = $code;
        $this->name = $name;
        $this->parents = new ArrayCollection();
        $this->children = new ArrayCollection();
    }

    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getTeamCode(): ?string
    {
        return $this->teamCode;
    }

    public function setTeamCode(?string $teamCode): void
    {
        $this->teamCode = $teamCode;
    }

    public function getTypeCode(): string
    {
        return \sprintf('%s_%s', $this->type, $this->code);
    }

    public function isCountry(): bool
    {
        return Zone::COUNTRY === $this->type;
    }

    public function isCity(): bool
    {
        return Zone::CITY === $this->type;
    }

    public function isBorough(): bool
    {
        return Zone::BOROUGH === $this->type;
    }

    public function isDepartment(): bool
    {
        return Zone::DEPARTMENT === $this->type;
    }

    public function isCityCommunity(): bool
    {
        return Zone::CITY_COMMUNITY === $this->type;
    }

    public function isRegion(): bool
    {
        return Zone::REGION === $this->type;
    }

    public function isDistrict(): bool
    {
        return Zone::DISTRICT === $this->type;
    }

    public function isCityGrouper(): bool
    {
        return \in_array($this->type, [
            self::CANTON,
            self::DISTRICT,
        ], true);
    }

    /**
     * @return self[]
     */
    public function getParents(): array
    {
        return $this->parents->toArray();
    }

    /**
     * @return self[]
     */
    public function getParentsOfType(string $type): array
    {
        return array_values(array_filter($this->parents->toArray(), function (Zone $zone) use ($type) {
            return $type === $zone->getType();
        }));
    }

    public function hasChild(Zone $child): bool
    {
        return $this->children->filter(function (Zone $zone) use ($child) {
            return $zone->getId() === $child->getId();
        })->count() > 0;
    }

    public function hasParent(Zone $parent): bool
    {
        return $this->parents->filter(function (Zone $zone) use ($parent) {
            return $zone->getId() === $parent->getId();
        })->count() > 0;
    }

    public function addParent(self $zone): void
    {
        $this->parents->contains($zone) || $this->parents->add($zone);
    }

    public function clearParents(): void
    {
        $this->parents->clear();
    }

    /**
     * @return self[]
     */
    public function getChildren(): array
    {
        return $this->children->toArray();
    }

    public function isInFrance(): bool
    {
        return
            !\in_array($this->type, [self::COUNTRY, self::FOREIGN_DISTRICT, self::CONSULAR_DISTRICT])
            || \in_array($this->code, AddressInterface::FRENCH_CODES, true);
    }

    /**
     * @return self[]
     */
    public function getWithParents(array $types = []): array
    {
        $parents = $this->parents->toArray();

        return array_merge(
            [$this],
            empty($types)
                ? $parents
                : array_filter($parents, function (Zone $zone) use ($types) {
                    return \in_array($zone->getType(), $types);
                })
        );
    }

    /**
     * @return string[]
     */
    public function getPostalCode(): ?array
    {
        return $this->postalCode;
    }

    public function getPostalCodeAsString(): string
    {
        return implode(', ', $this->getPostalCode());
    }

    /**
     * @param string[] $postalCode
     */
    public function setPostalCode(?array $postalCode): void
    {
        $this->postalCode = $postalCode;
    }

    public function hasTag(string $tag): bool
    {
        return \in_array($tag, $this->tags ?? [], true);
    }

    public function getTags(): ?array
    {
        return $this->tags;
    }

    public function isBoroughCity(): bool
    {
        return \in_array($this->code, [GeoInterface::CITY_PARIS_CODE, GeoInterface::CITY_LYON_CODE, GeoInterface::CITY_MARSEILLE_CODE], true);
    }

    public function isParis(): bool
    {
        return str_starts_with($this->code, '751');
    }

    public function getAssemblyZone(): ?self
    {
        foreach ($this->getWithParents() as $zone) {
            if ($zone->isAssemblyZone()) {
                return $zone;
            }
        }

        return null;
    }

    public function isAssemblyZone(): bool
    {
        return $this->hasTag(ZoneTagEnum::ASSEMBLY);
    }

    public function isFrance(): bool
    {
        return AddressInterface::FRANCE === $this->code;
    }
}
