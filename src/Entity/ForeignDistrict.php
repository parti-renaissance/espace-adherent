<?php

namespace App\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use App\Entity\Geo\Country;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ForeignDistrictRepository")
 *
 * @Algolia\Index(autoIndex=false)
 */
class ForeignDistrict implements ZoneInterface
{
    use ZoneTrait;

    /**
     * @var int
     *
     * @ORM\Column(type="smallint")
     */
    private $number;

    /**
     * @var Collection|Country[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Geo\Country", mappedBy="foreignDistrict")
     */
    private $countries;

    /**
     * @var Collection|ConsularDistrict[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\ConsularDistrict", mappedBy="foreignDistrict")
     */
    private $consularDistricts;

    public function __construct()
    {
        $this->countries = new ArrayCollection();
        $this->consularDistricts = new ArrayCollection();
    }

    public function getNumber(): int
    {
        return $this->number;
    }

    public function setNumber(int $number): void
    {
        $this->number = $number;
    }

    /**
     * @return Country[]|Collection
     */
    public function getCountries(): Collection
    {
        return $this->countries;
    }

    /**
     * @return ConsularDistrict[]|Collection
     */
    public function getConsularDistricts(): Collection
    {
        return $this->consularDistricts;
    }

    public function getParents(): array
    {
        return [];
    }
}
