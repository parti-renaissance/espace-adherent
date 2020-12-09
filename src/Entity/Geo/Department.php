<?php

namespace App\Entity\Geo;

use App\Entity\EntityTimestampableTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Geo\DepartmentRepository")
 * @ORM\Table(name="geo_department")
 */
class Department implements ZoneableInterface
{
    use GeoTrait;
    use EntityTimestampableTrait;

    /**
     * @var Region
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Geo\Region", inversedBy="departments")
     * @ORM\JoinColumn(nullable=false)
     *
     * @SymfonySerializer\Groups({"department_read"})
     */
    private $region;

    public function __construct(string $code, string $name, Region $region)
    {
        $this->code = $code;
        $this->name = $name;
        $this->region = $region;
    }

    public function getRegion(): Region
    {
        return $this->region;
    }

    public function setRegion(Region $region): void
    {
        $this->region = $region;
    }

    public function getParents(): array
    {
        return array_merge(
            [$this->region],
            $this->region->getParents(),
        );
    }

    public function getZoneType(): string
    {
        return Zone::DEPARTMENT;
    }
}
