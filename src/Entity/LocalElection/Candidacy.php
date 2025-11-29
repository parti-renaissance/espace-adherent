<?php

declare(strict_types=1);

namespace App\Entity\LocalElection;

use App\Repository\LocalElection\CandidacyRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity(repositoryClass: CandidacyRepository::class)]
#[ORM\Index(columns: ['email'])]
#[ORM\Table(name: 'local_election_candidacy')]
class Candidacy extends BaseLocalElectionCandidacy
{
    /**
     * @var CandidaciesGroup|null
     */
    #[Gedmo\SortableGroup]
    #[ORM\ManyToOne(targetEntity: CandidaciesGroup::class, inversedBy: 'candidacies')]
    protected $candidaciesGroup;
}
