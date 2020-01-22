<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ConsularDistrictRepository")
 * @ORM\Table(
 *     name="consular_districts",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="consular_district_code_unique", columns="code"),
 *         @ORM\UniqueConstraint(name="consular_district_referent_tag_unique", columns="referent_tag_id")
 *     }
 * )
 */
class ConsularDistrict
{
    /**
     * @var int|null
     *
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned": true})
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var array
     *
     * @ORM\Column(type="simple_array")
     */
    private $countries;

    /**
     * @var string
     *
     * @ORM\Column(length=6)
     */
    private $code;

    /**
     * @var int
     *
     * @ORM\Column(type="smallint", options={"unsigned": true})
     */
    private $number;

    /**
     * @var string
     *
     * @ORM\Column(length=50)
     */
    private $name;

    /**
     * @var GeoData
     *
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\GeoData", cascade={"all"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $geoData;

    /**
     * @var ReferentTag|null
     *
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\ReferentTag")
     */
    private $referentTag;

    public function __construct(
        array $countries,
        string $name,
        string $code,
        int $number,
        GeoData $geoData,
        ReferentTag $referentTag = null
    ) {
        $this->countries = $countries;
        $this->code = $code;
        $this->name = $name;
        $this->number = $number;
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

    public function update(array $countries, string $name, GeoData $geoData): self
    {
        $this->countries = $countries;
        $this->name = $name;
        $this->geoData = $geoData;

        return $this;
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
