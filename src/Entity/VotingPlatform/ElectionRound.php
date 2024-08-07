<?php

namespace App\Entity\VotingPlatform;

use App\Repository\VotingPlatform\ElectionRoundRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity(repositoryClass: ElectionRoundRepository::class)]
#[ORM\Table(name: 'voting_platform_election_round')]
class ElectionRound
{
    /**
     * @var int
     */
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    private $id;

    /**
     * @var UuidInterface
     */
    #[ORM\Column(type: 'uuid', unique: true)]
    protected $uuid;
    /**
     * @var Election
     */
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Election::class, inversedBy: 'electionRounds')]
    private $election;

    /**
     * @var ElectionPool[]|Collection
     */
    #[ORM\JoinTable(name: 'voting_platform_election_round_election_pool')]
    #[ORM\ManyToMany(targetEntity: ElectionPool::class, inversedBy: 'electionRounds', cascade: ['all'])]
    private $electionPools;

    /**
     * @var bool
     */
    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private $isActive;

    public function __construct(bool $isActive = true)
    {
        $this->isActive = $isActive;
        $this->uuid = Uuid::uuid4();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }

    public function getElection(): Election
    {
        return $this->election;
    }

    public function setElection(Election $election): void
    {
        $this->election = $election;
        $this->electionPools = new ArrayCollection();
    }

    public function addElectionPool(ElectionPool $pool): void
    {
        if (!$this->electionPools->contains($pool)) {
            $pool->addElectionRound($this);
            $this->electionPools->add($pool);
        }
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    /**
     * @return ElectionPool[]
     */
    public function getElectionPools(): array
    {
        return $this->electionPools->toArray();
    }

    public function disable(): void
    {
        $this->isActive = false;
    }

    public function setElectionPools(array $electionPools): void
    {
        $this->electionPools->clear();

        foreach ($electionPools as $pool) {
            $this->addElectionPool($pool);
        }
    }

    public function isRoundOf(Election $election): bool
    {
        return $election === $this->election;
    }

    public function equals(ElectionRound $round): bool
    {
        return $round->getId() === $this->id;
    }
}
