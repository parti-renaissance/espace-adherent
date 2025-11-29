<?php

declare(strict_types=1);

namespace App\Entity\Geo;

use App\Entity\EntityTimestampableTrait;
use App\Repository\Geo\DepartmentRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: DepartmentRepository::class)]
#[ORM\Table(name: 'geo_department')]
class Department implements ZoneableInterface
{
    use GeoTrait;
    use EntityTimestampableTrait;

    /**
     * @var Region
     */
    #[Groups(['department_read'])]
    #[ORM\JoinColumn(nullable: false)]
    #[ORM\ManyToOne(targetEntity: Region::class, inversedBy: 'departments')]
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
