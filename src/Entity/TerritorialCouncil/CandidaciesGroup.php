<?php

namespace App\Entity\TerritorialCouncil;

use App\Entity\VotingPlatform\Designation\BaseCandidaciesGroup;
use App\Entity\VotingPlatform\Designation\CandidacyInterface;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class CandidaciesGroup extends BaseCandidaciesGroup
{
    /**
     * @var CandidacyInterface[]|Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\TerritorialCouncil\Candidacy", mappedBy="candidaciesGroup")
     */
    protected $candidacies;
}
