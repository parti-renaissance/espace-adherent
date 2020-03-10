<?php

namespace AppBundle\Twig;

use AppBundle\Election\ElectionManager;
use AppBundle\Entity\City;
use AppBundle\Entity\Election\CityVoteResult;
use AppBundle\Entity\Election\MinistryVoteResult;
use Twig\Extension\RuntimeExtensionInterface;

class ElectionVoteResultRuntime implements RuntimeExtensionInterface
{
    private $electionManager;

    public function __construct(ElectionManager $electionManager)
    {
        $this->electionManager = $electionManager;
    }

    public function getCityVoteResult(City $city): ?CityVoteResult
    {
        return $this->electionManager->getCityVoteResultForCurrentElectionRound($city);
    }

    public function getMinistryVoteResult(City $city): ?MinistryVoteResult
    {
        return $this->electionManager->getMinistryVoteResultForCurrentElectionRound($city);
    }
}
