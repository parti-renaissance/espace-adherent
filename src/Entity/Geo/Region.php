<?php

declare(strict_types=1);

namespace App\Entity\Geo;

use App\Entity\EntityTimestampableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'geo_region')]
class Region implements ZoneableInterface
{
    use GeoTrait;
    use EntityTimestampableTrait;

    /**
     * @var Country
     */
    #[ORM\JoinColumn(nullable: false)]
    #[ORM\ManyToOne(targetEntity: Country::class)]
    private $country;

    /**
     * @var Collection|Department[]
     */
    #[ORM\OneToMany(mappedBy: 'region', targetEntity: Department::class)]
    private $departments;

    public function __construct(string $code, string $name, Country $country)
    {
        $this->code = $code;
        $this->name = $name;
        $this->country = $country;
        $this->departments = new ArrayCollection();
    }

    public function getCountry(): Country
    {
        return $this->country;
    }

    public function setCountry(Country $country): void
    {
        $this->country = $country;
    }

    /**
     * @return Department[]
     */
    public function getDepartments(): array
    {
        return $this->departments->toArray();
    }

    public function getParents(): array
    {
        return array_merge(
            [$this->country],
            $this->country->getParents(),
        );
    }

    public function getZoneType(): string
    {
        return Zone::REGION;
    }
}
