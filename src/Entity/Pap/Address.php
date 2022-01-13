<?php

namespace App\Entity\Pap;

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityZoneTrait;
use App\Entity\Geo\Zone;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Pap\AddressRepository")
 * @ORM\Table(name="pap_address", indexes={
 *     @ORM\Index(columns={"uuid"}),
 *     @ORM\Index(columns={"offset_x", "offset_y"}),
 *     @ORM\Index(columns={"latitude", "longitude"})
 * })
 *
 * @ApiResource(
 *     collectionOperations={},
 *     itemOperations={
 *         "get": {
 *             "method": "GET",
 *             "path": "/v3/pap/address/{id}",
 *             "requirements": {"id": "%pattern_uuid%"},
 *             "normalization_context": {"groups": {"pap_address_read"}},
 *         },
 *     },
 *     subresourceOperations={
 *         "voters_get_subresource": {
 *             "method": "GET",
 *             "path": "/v3/pap/address/{id}/voters",
 *             "requirements": {"id": "%pattern_uuid%"},
 *         },
 *     },
 * )
 */
class Address
{
    use EntityIdentityTrait;
    use EntityZoneTrait;

    /**
     * @var Collection|Zone[]
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Geo\Zone", cascade={"persist"})
     * @ORM\JoinTable(name="pap_address_zone")
     */
    protected $zones;

    /**
     * @ORM\Column(nullable=true)
     *
     * @Groups({"pap_address_list", "pap_address_read", "pap_campaign_history_read_list"})
     */
    private ?string $number;

    /**
     * @ORM\Column(nullable=true)
     *
     * @Groups({"pap_address_list", "pap_address_read", "pap_campaign_history_read_list"})
     */
    private ?string $address;

    /**
     * @ORM\Column(length=5, nullable=true)
     *
     * @Groups({"pap_address_list", "pap_address_read"})
     */
    private ?string $inseeCode;

    /**
     * @ORM\Column(type="simple_array", nullable=true)
     *
     * @Groups({"pap_address_list", "pap_address_read", "pap_campaign_history_read_list"})
     */
    private ?array $postalCodes;

    /**
     * @ORM\Column(nullable=true)
     *
     * @Groups({"pap_address_list", "pap_address_read", "pap_campaign_history_read_list"})
     */
    private ?string $cityName;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $offsetX;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $offsetY;

    /**
     * @ORM\Column(type="geo_point", nullable=true)
     *
     * @Groups({"pap_address_list"})
     */
    private ?float $latitude;

    /**
     * @ORM\Column(type="geo_point", nullable=true)
     *
     * @Groups({"pap_address_list"})
     */
    private ?float $longitude;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Pap\Voter", mappedBy="address", cascade={"all"}, fetch="EXTRA_LAZY")
     *
     * @ApiSubresource
     */
    private Collection $voters;

    /**
     * @ORM\Column(type="smallint", options={"unsigned": true, "default": 0})
     *
     * @Groups({"pap_address_list", "pap_address_read"})
     */
    private int $votersCount = 0;

    /**
     * @var Building[]|Collection
     *
     * @ORM\OneToOne(
     *     targetEntity="App\Entity\Pap\Building",
     *     mappedBy="address",
     *     cascade={"all"},
     *     orphanRemoval=true
     * )
     *
     * @Groups({"pap_address_list"})
     */
    private ?Building $building = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Pap\VotePlace")
     */
    public ?VotePlace $votePlace = null;

    public function __construct(
        UuidInterface $uuid = null,
        string $number = null,
        string $address = null,
        string $inseeCode = null,
        array $postalCodes = null,
        string $cityName = null,
        int $offsetX = null,
        int $offsetY = null,
        float $latitude = null,
        float $longitude = null
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
        $this->zones = new ArrayCollection();
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
}
