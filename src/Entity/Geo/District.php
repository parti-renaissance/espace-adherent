<?php

declare(strict_types=1);

namespace App\Entity\Geo;

use App\Entity\EntityTimestampableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'geo_district')]
class District implements ZoneableInterface
{
    use GeoTrait;
    use EntityTimestampableTrait;

    /**
     * @var int
     */
    #[ORM\Column(type: 'smallint')]
    private $number;

    /**
     * @var Department
     */
    #[ORM\JoinColumn(nullable: false)]
    #[ORM\ManyToOne(targetEntity: Department::class)]
    private $department;

    /**
     * @var Collection|City[]
     */
    #[ORM\ManyToMany(targetEntity: City::class, mappedBy: 'districts')]
    private $cities;

    public function __construct(string $code, string $name, int $number, Department $department)
    {
        $this->code = $code;
        $this->name = $name;
        $this->number = $number;
        $this->department = $department;
        $this->cities = new ArrayCollection();
    }

    public function getNumber(): int
    {
        return $this->number;
    }

    public function getDepartment(): Department
    {
        return $this->department;
    }

    /**
     * @return City[]
     */
    public function getCities(): array
    {
        return $this->cities->toArray();
    }

    public function addCity(City $city): void
    {
        if (!$this->cities->contains($city)) {
            $this->cities->add($city);
            $city->addDistrict($this);
        }
    }

    public function clearCities(): void
    {
        $this->cities->clear();
    }

    public function getParents(): array
    {
        $parents = [];

        $parents[] = $department = $this->getDepartment();
        if ($department) {
            $parents = array_merge($parents, $department->getParents());

            if (self::DEPARTMENT_PARIS_CODE === $department->getCode()) {
                $parents = array_merge($parents, $this->cities->filter(function (City $city) {
                    return self::CITY_PARIS_CODE === $city->getCode();
                })->toArray());
            }
        }

        return array_values(array_unique(array_filter($parents)));
    }

    public function getZoneType(): string
    {
        return Zone::DISTRICT;
    }
}
