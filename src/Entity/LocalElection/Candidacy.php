<?php

namespace App\Entity\LocalElection;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="App\Repository\LocalElection\CandidacyRepository")
 * @ORM\Table(
 *     name="local_election_candidacy",
 *     indexes={
 *         @ORM\Index(columns={"email"}),
 *     }
 * )
 */
class Candidacy extends BaseLocalElectionCandidacy
{
    /**
     * @var CandidaciesGroup|null
     *
     * @Gedmo\SortableGroup
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\LocalElection\CandidaciesGroup", inversedBy="candidacies")
     */
    protected $candidaciesGroup;
}
