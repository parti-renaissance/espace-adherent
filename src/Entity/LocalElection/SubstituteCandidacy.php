<?php

declare(strict_types=1);

namespace App\Entity\LocalElection;

use App\Repository\LocalElection\SubstituteCandidacyRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity(repositoryClass: SubstituteCandidacyRepository::class)]
#[ORM\Index(columns: ['email'])]
#[ORM\Table(name: 'local_election_substitute_candidacy')]
class SubstituteCandidacy extends BaseLocalElectionCandidacy
{
    /**
     * @var CandidaciesGroup|null
     */
    #[Gedmo\SortableGroup]
    #[ORM\ManyToOne(targetEntity: CandidaciesGroup::class, inversedBy: 'substituteCandidacies')]
    protected $candidaciesGroup;
}
