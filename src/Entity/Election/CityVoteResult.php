<?php

namespace App\Entity\Election;

use App\Entity\City;
use App\Entity\ElectionRound;
use App\Repository\Election\CityVoteResultRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CityVoteResultRepository::class)]
class CityVoteResult extends BaseWithListCollectionResult
{
    /**
     * @var City
     */
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\OneToOne(targetEntity: City::class)]
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
