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
     * @var array
     *
     * @ORM\Column(type="simple_array")
     */
    private $cities;

    /**
     * @var string
     *
     * @ORM\Column(length=6)
     */
    private $code;

    public function __construct(array $countries, array $cities, string $code)
    {
        $this->countries = $countries;
        $this->cities = $cities;
        $this->code = $code;
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

    public function getCities(): array
    {
        return $this->cities;
    }

    public function setCities(array $cities): void
    {
        $this->cities = $cities;
    }

    public function getCitiesAsString(): string
    {
        return implode(', ', $this->cities);
    }

    public function setCitiesAsString(string $cities): void
    {
        $this->cities = array_map('trim', explode(',', $cities));
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function update(array $countries): self
    {
        $this->countries = $countries;

        return $this;
    }

    public function getFullName(): string
    {
        return sprintf(
            '%s, %s%s circonscription',
            $this->getCountriesAsString,
            $this->code,
            1 === $this->code ? 'ère' : 'ème'
        );
    }

    public function __toString(): string
    {
        return $this->getFullName();
    }
}
