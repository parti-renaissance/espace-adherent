<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'procuration_proxies_to_election_rounds')]
#[ORM\UniqueConstraint(name: 'procuration_proxy_election_round_unique', columns: ['procuration_proxy_id', 'election_round_id'])]
class ProcurationProxyElectionRound
{
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    private ?int $id = null;

    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: ProcurationProxy::class, inversedBy: 'procurationProxyElectionRounds')]
    private ProcurationProxy $procurationProxy;

    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: ElectionRound::class)]
    private ElectionRound $electionRound;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private bool $frenchRequestAvailable = true;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private bool $foreignRequestAvailable = true;

    public function __construct(ProcurationProxy $procurationProxy, ElectionRound $electionRound)
    {
        $this->procurationProxy = $procurationProxy;
        $this->electionRound = $electionRound;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProcurationProxy(): ProcurationProxy
    {
        return $this->procurationProxy;
    }

    public function setProcurationProxy(ProcurationProxy $procurationProxy): void
    {
        $this->procurationProxy = $procurationProxy;
    }

    public function getElectionRound(): ElectionRound
    {
        return $this->electionRound;
    }

    public function setElectionRound(ElectionRound $electionRound): void
    {
        $this->electionRound = $electionRound;
    }

    public function isFrenchRequestAvailable(): bool
    {
        return $this->frenchRequestAvailable;
    }

    public function setFrenchRequestAvailable(bool $available): void
    {
        $this->frenchRequestAvailable = $available;
    }

    public function isForeignRequestAvailable(): bool
    {
        return $this->foreignRequestAvailable;
    }

    public function setForeignRequestAvailable(bool $available): void
    {
        $this->foreignRequestAvailable = $available;
    }
}
