<?php

namespace App\Entity\Geo;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use App\Entity\EntityTimestampableTrait;
use App\Entity\ZoneInterface;
use App\Entity\ZoneTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="geo_district")
 *
 * @Algolia\Index(autoIndex=false)
 */
class District implements ZoneInterface
{
    use ZoneTrait;
    use ActivableTrait;
    use EntityTimestampableTrait;

    /**
     * @var Department
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Geo\Department")
     * @ORM\JoinColumn(nullable=false)
     */
    private $department;

    public function __construct(string $code, string $name, Department $department)
    {
        $this->code = $code;
        $this->name = $name;
        $this->department = $department;
    }

    public function getDepartment(): Department
    {
        return $this->department;
    }

    public function setDepartment(Department $department): void
    {
        $this->department = $department;
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
