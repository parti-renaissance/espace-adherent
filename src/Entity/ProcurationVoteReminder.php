<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class ProcurationVoteReminder
{
    use EntityTimestampableTrait;

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    private ?int $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private ?\DateTimeInterface $processedAt;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ProcurationRequest")
     */
    private ProcurationRequest $procurationRequest;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ElectionRound")
     */
    private ElectionRound $electionRound;

    public function __construct(ProcurationRequest $procurationRequest, ElectionRound $electionRound)
    {
        $this->procurationRequest = $procurationRequest;
        $this->electionRound = $electionRound;
        $this->processedAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProcurationRequest(): ProcurationRequest
    {
        return $this->procurationRequest;
    }

    public function getElectionRound(): ElectionRound
    {
        return $this->electionRound;
    }
}
