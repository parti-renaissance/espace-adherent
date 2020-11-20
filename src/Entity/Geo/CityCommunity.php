<?php

namespace App\Entity\Geo;

use App\Entity\EntityTimestampableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="geo_city_community")
 */
class CityCommunity implements ZoneableInterface
{
    use GeoTrait;
    use EntityTimestampableTrait;

    /**
     * @var Collection|Department[]
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Geo\Department")
     * @ORM\JoinTable(name="geo_city_community_department")
     */
    private $departments;

    public function __construct(string $code, string $name)
    {
        $this->code = $code;
        $this->name = $name;
        $this->departments = new ArrayCollection();
    }

    public function getDepartments(): Collection
    {
        return $this->departments;
    }

    public function addDepartment(Department $department): void
    {
        $this->departments->contains($department) || $this->departments->add($department);
    }

    public function clearDepartments(): void
    {
        $this->departments->clear();
    }

    public function getParents(): array
    {
        $toMerge = [];

        foreach ($this->departments as $department) {
            $toMerge[] = [$department];
            $toMerge[] = $department->getParents();
        }

        return $toMerge ? array_values(array_unique(array_merge(...$toMerge))) : [];
    }

    public function getZoneType(): string
    {
        return Zone::CITY_COMMUNITY;
    }
}
