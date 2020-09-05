<?php

namespace App\Entity\Geo;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use App\Entity\EntityTimestampableTrait;
use App\Entity\ZoneInterface;
use App\Entity\ZoneTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="geo_city")
 *
 * @Algolia\Index(autoIndex=false)
 */
class City implements ZoneInterface
{
    use ZoneTrait;
    use ActivableTrait;
    use EntityTimestampableTrait;

    /**
     * @var string[]|null
     *
     * @ORM\Column(type="simple_array", nullable=true)
     */
    private $postalCode;

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $population;

    /**
     * @var Department|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Geo\Department")
     * @ORM\JoinColumn(nullable=true)
     */
    private $department;

    /**
     * @var Canton[]|Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Geo\Canton", inversedBy="cities")
     * @ORM\JoinTable(name="geo_city_canton")
     */
    private $cantons;

    /**
     * @var CityCommunity|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Geo\CityCommunity")
     * @ORM\JoinColumn(nullable=true)
     */
    private $cityCommunity;

    /**
     * @var District|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Geo\District")
     * @ORM\JoinColumn(nullable=true)
     */
    private $district;

    public function __construct(string $code, string $name)
    {
        $this->code = $code;
        $this->name = $name;
        $this->cantons = new ArrayCollection();
    }

    /**
     * @return string[]
     */
    public function getPostalCode(): array
    {
        return $this->postalCode ?: [];
    }

    /**
     * @param string[] $postalCode
     */
    public function setPostalCode(array $postalCode): void
    {
        $this->postalCode = $postalCode;
    }

    public function getPopulation(): ?int
    {
        return $this->population;
    }

    public function setPopulation(?int $population): void
    {
        $this->population = $population;
    }

    public function getDepartment(): ?Department
    {
        return $this->department;
    }

    public function setDepartment(?Department $department): void
    {
        $this->department = $department;
    }

    /**
     * @return Canton[]|Collection
     */
    public function getCantons(): Collection
    {
        return $this->cantons;
    }

    public function getCityCommunity(): ?CityCommunity
    {
        return $this->cityCommunity;
    }

    public function setCityCommunity(?CityCommunity $cityCommunity): void
    {
        $this->cityCommunity = $cityCommunity;
    }

    public function getDistrict(): ?District
    {
        return $this->district;
    }

    public function setDistrict(?District $district): void
    {
        $this->district = $district;
    }

    public function getParents(): array
    {
        $parents = [];

        $parents[] = $department = $this->getDepartment();
        if ($department) {
            $parents = array_merge($parents, $department->getParents());
        }

        $cantons = $this->getCantons();
        foreach ($cantons as $canton) {
            $parents[] = $canton;
            $parents = array_merge($parents, $canton->getParents());
        }

        return $this->sanitizeEntityList($parents);
    }
}
