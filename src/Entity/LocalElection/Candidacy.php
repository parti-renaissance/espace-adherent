<?php

namespace App\Entity\LocalElection;

use App\Repository\LocalElection\CandidacyRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Table(name: 'local_election_candidacy')]
#[ORM\Index(columns: ['email'])]
#[ORM\Entity(repositoryClass: CandidacyRepository::class)]
class Candidacy extends BaseLocalElectionCandidacy
{
    /**
     * @var CandidaciesGroup|null
     */
    #[ORM\ManyToOne(targetEntity: CandidaciesGroup::class, inversedBy: 'candidacies')]
    #[Gedmo\SortableGroup]
    protected $candidaciesGroup;
}
