<?php

namespace App\Entity\TerritorialCouncil;

use App\Entity\VotingPlatform\Designation\BaseCandidaciesGroup;
use App\Entity\VotingPlatform\Designation\CandidacyInterface;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'territorial_council_candidacies_group')]
#[ORM\Entity]
class CandidaciesGroup extends BaseCandidaciesGroup
{
    /**
     * @var CandidacyInterface[]|Collection
     */
    #[ORM\OneToMany(mappedBy: 'candidaciesGroup', targetEntity: Candidacy::class)]
    protected $candidacies;
}
