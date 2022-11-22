<?php

namespace App\Entity\LocalElection;

use App\Entity\Adherent;
use App\Entity\VotingPlatform\Designation\BaseCandidaciesGroup;
use App\Entity\VotingPlatform\Designation\BaseCandidacy;
use App\Entity\VotingPlatform\Designation\ElectionEntityInterface;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Runroom\SortableBehaviorBundle\Behaviors\Sortable;

/**
 * @ORM\Entity
 * @ORM\Table(name="local_election_candidacy")
 */
class Candidacy extends BaseCandidacy
{
    use Sortable;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\LocalElection\LocalElection")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    public ?LocalElection $election = null;

    /**
     * @var CandidaciesGroup|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\LocalElection\CandidaciesGroup", inversedBy="candidacies")
     */
    protected $candidaciesGroup;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Adherent")
     */
    private ?Adherent $adherent = null;

    /** @ORM\Column */
    private ?string $firstName = null;

    /** @ORM\Column */
    private ?string $lastName = null;

    /** @ORM\Column */
    private ?string $email = null;

    public function __construct(LocalElection $election = null, string $gender = null, UuidInterface $uuid = null)
    {
        parent::__construct($gender, $uuid);

        $this->election = $election;
    }

    public function getElection(): ElectionEntityInterface
    {
        return $this->election;
    }

    public function setElection(LocalElection $election): void
    {
        $this->election = $election;
    }

    protected function createCandidaciesGroup(): BaseCandidaciesGroup
    {
        return new CandidaciesGroup();
    }

    public function getType(): string
    {
        return $this->getElection()->getDesignationType();
    }

    public function getAdherent(): ?Adherent
    {
        return $this->adherent;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }
}
