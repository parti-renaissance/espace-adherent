<?php

declare(strict_types=1);

namespace App\Entity\Pap;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\Collection\ZoneCollection;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityZoneTrait;
use App\Entity\Geo\Zone;
use App\Repository\Pap\AddressRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/v3/pap/address/{uuid}',
            requirements: ['uuid' => '%pattern_uuid%']
        ),
    ],
    normalizationContext: ['iri' => true, 'groups' => ['pap_address_read']]
)]
#[ORM\Entity(repositoryClass: AddressRepository::class)]
#[ORM\Index(columns: ['offset_x', 'offset_y'])]
#[ORM\Index(columns: ['latitude', 'longitude'])]
#[ORM\Table(name: 'pap_address')]
class Address
{
    use EntityIdentityTrait;
    use EntityZoneTrait;

    #[ORM\JoinTable(name: 'pap_address_zone')]
    #[ORM\ManyToMany(targetEntity: Zone::class, cascade: ['persist'])]
    protected Collection $zones;

    #[Groups(['pap_address_list', 'pap_address_read', 'pap_campaign_history_read_list', 'pap_building_statistics_read'])]
    #[ORM\Column(nullable: true)]
    private ?string $number;

    #[Groups(['pap_address_list', 'pap_address_read', 'pap_campaign_history_read_list', 'pap_building_statistics_read'])]
    #[ORM\Column(nullable: true)]
    private ?string $address;

    #[Groups(['pap_address_list', 'pap_address_read', 'pap_building_statistics_read'])]
    #[ORM\Column(length: 5, nullable: true)]
    private ?string $inseeCode;

    #[Groups(['pap_address_list', 'pap_address_read', 'pap_campaign_history_read_list', 'pap_building_statistics_read'])]
    #[ORM\Column(type: 'simple_array', nullable: true)]
    private ?array $postalCodes;

    #[Groups(['pap_address_list', 'pap_address_read', 'pap_campaign_history_read_list', 'pap_building_statistics_read'])]
    #[ORM\Column(nullable: true)]
    private ?string $cityName;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $offsetX;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $offsetY;

    #[Groups(['pap_address_list', 'pap_address_read'])]
    #[ORM\Column(type: 'geo_point', nullable: true)]
    private ?float $latitude;

    #[Groups(['pap_address_list', 'pap_address_read'])]
    #[ORM\Column(type: 'geo_point', nullable: true)]
    private ?float $longitude;

    #[ORM\OneToMany(mappedBy: 'address', targetEntity: Voter::class, cascade: ['all'], fetch: 'EXTRA_LAZY')]
    private Collection $voters;

    #[Groups(['pap_address_list', 'pap_address_read'])]
    #[ORM\Column(type: 'smallint', options: ['unsigned' => true, 'default' => 0])]
    private int $votersCount = 0;

    /**
     * @var Building[]|Collection
     */
    #[Groups(['pap_address_list', 'pap_address_read'])]
    #[ORM\OneToOne(mappedBy: 'address', targetEntity: Building::class, cascade: ['all'], orphanRemoval: true)]
    private ?Building $building = null;

    #[ORM\ManyToOne(targetEntity: VotePlace::class)]
    public ?VotePlace $votePlace = null;

    #[Groups(['pap_address_list', 'pap_address_read'])]
    #[ORM\Column(type: 'smallint', nullable: true, options: ['unsigned' => true])]
    public ?int $priority = null;

    public function __construct(
        ?UuidInterface $uuid = null,
        ?string $number = null,
        ?string $address = null,
        ?string $inseeCode = null,
        ?array $postalCodes = null,
        ?string $cityName = null,
        ?int $offsetX = null,
        ?int $offsetY = null,
        ?float $latitude = null,
        ?float $longitude = null,
    ) {
        $this->uuid = $uuid ?? Uuid::uuid4();
        $this->number = $number;
        $this->address = $address;
        $this->inseeCode = $inseeCode;
        $this->postalCodes = $postalCodes;
        $this->cityName = $cityName;
        $this->offsetX = $offsetX;
        $this->offsetY = $offsetY;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->voters = new ArrayCollection();
        $this->zones = new ZoneCollection();
    }

    public function getNumber(): ?string
    {
        return $this->number;
    }

    public function setNumber(?string $number): void
    {
        $this->number = $number;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): void
    {
        $this->address = $address;
    }

    public function getInseeCode(): ?string
    {
        return $this->inseeCode;
    }

    public function setInseeCode(?string $inseeCode): void
    {
        $this->inseeCode = $inseeCode;
    }

    public function getCityName(): ?string
    {
        return $this->cityName;
    }

    public function setCityName(?string $cityName): void
    {
        $this->cityName = $cityName;
    }

    public function getOffsetX(): ?int
    {
        return $this->offsetX;
    }

    public function setOffsetX(?int $offsetX): void
    {
        $this->offsetX = $offsetX;
    }

    public function getOffsetY(): ?int
    {
        return $this->offsetY;
    }

    public function setOffsetY(?int $offsetY): void
    {
        $this->offsetY = $offsetY;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(?float $latitude): void
    {
        $this->latitude = $latitude;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(?float $longitude): void
    {
        $this->longitude = $longitude;
    }

    public function getVoters(): Collection
    {
        return $this->voters;
    }

    public function addVoter(Voter $voter): void
    {
        if (!$this->voters->contains($voter)) {
            $voter->setAddress($this);
            $this->voters->add($voter);

            $this->votersCount = $this->voters->count();
        }
    }

    public function removeVoter(Voter $voter): void
    {
        $this->voters->removeElement($voter);
    }

    public function getVotersCount(): int
    {
        return $this->votersCount;
    }

    public function getBuilding(): ?Building
    {
        return $this->building;
    }

    public function setBuilding(Building $building): void
    {
        $building->setAddress($this);
        $this->building = $building;
    }

    public function getPostalCodes(): ?array
    {
        return $this->postalCodes;
    }

    public function getPostalCodesAsString(): string
    {
        return implode(', ', $this->postalCodes);
    }
}
