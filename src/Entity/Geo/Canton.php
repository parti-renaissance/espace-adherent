<?php

declare(strict_types=1);

namespace App\Entity\Geo;

use App\Entity\EntityTimestampableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'geo_canton')]
class Canton implements ZoneableInterface
{
    use GeoTrait;
    use EntityTimestampableTrait;

    /**
     * @var Department
     */
    #[ORM\JoinColumn(nullable: false)]
    #[ORM\ManyToOne(targetEntity: Department::class)]
    private $department;

    /**
     * @var City[]|Collection
     */
    #[ORM\ManyToMany(targetEntity: City::class, mappedBy: 'cantons')]
    private $cities;

    public function __construct(string $code, string $name, Department $department)
    {
        $this->code = $code;
        $this->name = $name;
        $this->department = $department;
        $this->cities = new ArrayCollection();
    }

    public function getDepartment(): Department
    {
        return $this->department;
    }

    public function setDepartment(Department $department): void
    {
        $this->department = $department;
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
        }

        $city->addCanton($this);
    }

    public function clearCities(): void
    {
        $this->cities->clear();
    }

    public function getParents(): array
    {
        return array_merge(
            [$this->department],
            $this->department->getParents(),
        );
    }

    public function getZoneType(): string
    {
        return Zone::CANTON;
    }
}
