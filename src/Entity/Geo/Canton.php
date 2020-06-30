<?php

namespace App\Entity\Geo;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use App\Entity\EntityTimestampableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="geo_canton")
 *
 * @Algolia\Index(autoIndex=false)
 */
class Canton implements CollectivityInterface
{
    use GeoTrait;
    use EntityTimestampableTrait;

    /**
     * @var Department
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Geo\Department")
     * @ORM\JoinColumn(nullable=false)
     */
    private $department;

    /**
     * @var City[]|Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Geo\City", mappedBy="cantons")
     */
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
     * @return City[]|Collection
     */
    public function getCities(): Collection
    {
        return $this->cities;
    }

    public function getParents(): array
    {
        $parents = [];

        $parents[] = $department = $this->getDepartment();
        if ($department) {
            $parents = array_merge($parents, $department->getParents());
        }

        return $this->sanitizeEntityList($parents);
    }
}
