<?php

namespace AppBundle\Entity;

use CrEOF\Spatial\PHP\Types\Geometry\GeometryInterface;
use CrEOF\Spatial\PHP\Types\Geometry\MultiPolygon;
use CrEOF\Spatial\PHP\Types\Geometry\Polygon;
use Doctrine\ORM\Mapping as ORM;

/**
 * References :
 *  - France: https://public.opendatasoft.com/explore/dataset/circonscriptions-legislatives-2017/export (JSON format)
 *  - Other countries: https://gist.github.com/angelodlfrtr/cf39d7db97c335f87d22
 *
 * @ORM\Entity
 * @ORM\Table(
 *     name="districts",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="district_code_unique", columns="code"),
 *         @ORM\UniqueConstraint(name="district_department_code_number", columns={"department_code", "number"})
 *     }
 * )
 */
class District
{
    public const FRANCE = 'FR';

    /**
     * @var int|null
     *
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned": true})
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * The managed district countries.
     *
     * @var array
     *
     * @ORM\Column(type="simple_array")
     */
    private $countries = [];

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
     * @var string
     *
     * @ORM\Column(length=5)
     */
    private $departmentCode;

    /**
     * @var Polygon|MultiPolygon
     *
     * @ORM\Column(type="geometry")
     */
    private $geoShape;

    /**
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Adherent", inversedBy="managedDistrict")
     */
    private $adherent;

    /**
     * @throws \InvalidArgumentException if $geoShape is invalid
     */
    public function __construct(
        array $countries,
        string $name,
        string $code,
        int $number,
        string $departmentCode,
        GeometryInterface $geoShape
    ) {
        if (!$geoShape instanceof Polygon && !$geoShape instanceof MultiPolygon) {
            throw new \InvalidArgumentException(
                sprintf('$geoShape must be an instance of %s or %s, %s given', Polygon::class, MultiPolygon::class, get_class($geoShape))
            );
        }

        $this->countries = $countries;
        $this->code = $code;
        $this->name = $name;
        $this->number = $number;
        $this->departmentCode = $departmentCode;
        $this->geoShape = $geoShape;
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
        return self::FRANCE === $this->getCountriesAsString();
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

    /**
     * @return MultiPolygon|Polygon
     */
    public function getGeoShape(): GeometryInterface
    {
        return $this->geoShape;
    }

    public function getPolygons(): array
    {
        if (GeometryInterface::MULTIPOLYGON === $this->geoShape->getType()) {
            return array_merge(...$this->geoShape->toArray());
        }

        return $this->geoShape->toArray();
    }

    public function getAdherent(): ?Adherent
    {
        return $this->adherent;
    }

    public function setAdherent(?Adherent $adherent)
    {
        $this->adherent = $adherent;
    }

    public function update(array $countries, string $name, GeometryInterface $geoShape): self
    {
        $this->countries = $countries;
        $this->name = $name;
        $this->geoShape = $geoShape;

        return $this;
    }

    public function __toString(): string
    {
        return sprintf('%s, %sème circonscription (%s)', $this->name, $this->number, substr_replace($this->code, '-', -3, 1));
    }
}
