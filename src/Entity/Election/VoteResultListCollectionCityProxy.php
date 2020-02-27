<?php

namespace AppBundle\Entity\Election;

use AppBundle\Entity\City;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class VoteResultListCollectionCityProxy
{
    /**
     * @var int|null
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Election\VoteResultListCollection", inversedBy="cityProxies")
     */
    private $listCollection;

    /**
     * @var City
     *
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\City")
     */
    private $city;

    public function __construct(VoteResultListCollection $listCollection, City $city)
    {
        $this->listCollection = $listCollection;
        $this->city = $city;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getListCollection()
    {
        return $this->listCollection;
    }

    public function setListCollection($listCollection): void
    {
        $this->listCollection = $listCollection;
    }

    public function getCity(): City
    {
        return $this->city;
    }

    public function setCity(City $city): void
    {
        $this->city = $city;
    }
}
