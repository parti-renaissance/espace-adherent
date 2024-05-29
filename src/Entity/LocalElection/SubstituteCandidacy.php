<?php

namespace App\Entity\LocalElection;

use App\Repository\LocalElection\SubstituteCandidacyRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Table(name: 'local_election_substitute_candidacy')]
#[ORM\Index(columns: ['email'])]
#[ORM\Entity(repositoryClass: SubstituteCandidacyRepository::class)]
class SubstituteCandidacy extends BaseLocalElectionCandidacy
{
    /**
     * @var CandidaciesGroup|null
     *
     * @Gedmo\SortableGroup
     */
    #[ORM\ManyToOne(targetEntity: CandidaciesGroup::class, inversedBy: 'substituteCandidacies')]
    protected $candidaciesGroup;
}
