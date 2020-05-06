<?php

namespace App\Entity\Election;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use App\Entity\City;
use App\Entity\ElectionRound;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Election\CityVoteResultRepository")
 *
 * @Algolia\Index(autoIndex=false)
 */
class CityVoteResult extends BaseWithListCollectionResult
{
    /**
     * @var City
     *
     * @ORM\OneToOne(targetEntity="App\Entity\City")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $city;

    public function __construct(City $city, ElectionRound $electionRound)
    {
        parent::__construct($electionRound);

        $this->city = $city;
    }

    public function getCity(): ?City
    {
        return $this->city;
    }

    public function setCity(City $city): void
    {
        $this->city = $city;
    }
}
