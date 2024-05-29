<?php

namespace App\Entity\Instance\NationalCouncil;

use App\Entity\VotingPlatform\Designation\AbstractElectionEntity;
use App\Entity\VotingPlatform\Designation\Designation;
use App\Repository\Instance\NationalCouncil\ElectionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;

#[ORM\Table(name: 'national_council_election')]
#[ORM\Entity(repositoryClass: ElectionRepository::class)]
class Election extends AbstractElectionEntity
{
    /**
     * @var Candidacy[]|Collection
     */
    #[ORM\OneToMany(mappedBy: 'election', targetEntity: Candidacy::class)]
    private $candidacies;

    public function __construct(?Designation $designation = null, ?UuidInterface $uuid = null)
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
