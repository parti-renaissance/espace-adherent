<?php

namespace App\Entity;

use App\Address\AddressInterface;
use App\Repository\DistrictRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * References :
 *  - France: https://www.data.gouv.fr/s/resources/contours-precis-des-circonscriptions-legislatives/20170511-183720/circonscriptions-legislatives.json
 *  - Other countries: https://raw.githubusercontent.com/datasets/geo-countries/master/data/countries.geojson
 */
#[ORM\Table(name: 'districts')]
#[ORM\UniqueConstraint(name: 'district_department_code_number', columns: ['department_code', 'number'])]
#[ORM\Entity(repositoryClass: DistrictRepository::class)]
class District
{
    /**
     * @var int|null
     */
    #[ORM\Id]
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    #[ORM\GeneratedValue]
    private $id;

    /**
     * The managed district countries.
     *
     * @var array
     */
    #[ORM\Column(type: 'simple_array')]
    private $countries;

    /**
     * @var string
     */
    #[ORM\Column(length: 6, unique: true)]
    private $code;

    /**
     * @var int
     */
    #[ORM\Column(type: 'smallint', options: ['unsigned' => true])]
    private $number;

    /**
     * @var string
     */
    #[ORM\Column(length: 50)]
    private $name;

    /**
     * @var string
     */
    #[ORM\Column(length: 5)]
    private $departmentCode;

    /**
     * @var GeoData
     */
    #[ORM\JoinColumn(nullable: false)]
    #[ORM\OneToOne(targetEntity: GeoData::class, cascade: ['all'])]
    private $geoData;

    /**
     * @var ReferentTag|null
     */
    #[ORM\JoinColumn(unique: true)]
    #[ORM\OneToOne(targetEntity: ReferentTag::class)]
    private $referentTag;

    public function __construct(
        array $countries,
        string $name,
        string $code,
        int $number,
        string $departmentCode,
        GeoData $geoData,
        ?ReferentTag $referentTag = null
    ) {
        $this->countries = $countries;
        $this->code = $code;
        $this->name = $name;
        $this->number = $number;
        $this->departmentCode = $departmentCode;
        $this->geoData = $geoData;
        $this->referentTag = $referentTag;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCountries(): array
    {
        return $this->countries;
    }

    public function setCountries(array $countries): void
    {
        $this->countries = $countries;
    }

    public function getCountriesAsString(): string
    {
        return implode(', ', $this->countries);
    }

    public function setCountriesAsString(string $countries): void
    {
        $this->countries = array_map('trim', explode(',', $countries));
    }

    public function isFrenchDistrict(): bool
    {
        return AddressInterface::FRANCE === $this->getCountriesAsString();
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getNumber(): int
    {
        return $this->number;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDepartmentCode(): string
    {
        return $this->departmentCode;
    }

    public function getGeoData(): GeoData
    {
        return $this->geoData;
    }

    public function setGeoData(GeoData $geoData): void
    {
        $this->geoData = $geoData;
    }

    public function getReferentTag(): ?ReferentTag
    {
        return $this->referentTag;
    }

    public function setReferentTag(ReferentTag $referentTag): void
    {
        $this->referentTag = $referentTag;
    }

    public function getFullName(): string
    {
        return sprintf(
            '%s, %s%s circonscription (%s)',
            $this->name,
            $this->number,
            1 === $this->number ? 'ère' : 'ème',
            substr_replace($this->code, '-', -3, 1)
        );
    }

    public function __toString(): string
    {
        return $this->getFullName();
    }
}
