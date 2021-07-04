<?php

namespace App\Entity\Instance\NationalCouncil;

use App\Entity\VotingPlatform\Designation\AbstractElectionEntity;
use App\Entity\VotingPlatform\Designation\Designation;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Instance\NationalCouncil\ElectionRepository")
 * @ORM\Table(name="national_council_election")
 */
class Election extends AbstractElectionEntity
{
    /**
     * @var Candidacy[]|Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Instance\NationalCouncil\Candidacy", mappedBy="election")
     */
    private $candidacies;

    public function __construct(Designation $designation = null, UuidInterface $uuid = null)
    {
        parent::__construct($designation, $uuid);

        $this->candidacies = new ArrayCollection();
    }

    /**
     * @return Candidacy[]
     */
    public function getCandidacies(): array
    {
        return $this->candidacies->toArray();
    }
}
