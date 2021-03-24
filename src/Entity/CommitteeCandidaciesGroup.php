<?php

namespace App\Entity;

use App\Entity\VotingPlatform\Designation\BaseCandidaciesGroup;
use App\Entity\VotingPlatform\Designation\CandidacyInterface;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class CommitteeCandidaciesGroup extends BaseCandidaciesGroup
{
    /**
     * @var CandidacyInterface[]|Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\CommitteeCandidacy", mappedBy="candidaciesGroup")
     */
    protected $candidacies;
}
