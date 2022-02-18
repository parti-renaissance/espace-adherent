<?php

namespace App\Entity\Procuration;

use App\Entity\ElectionRound;
use App\Entity\ProcurationRequest;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="procuration_reminder")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({
 *     "proxy_vote": "App\Entity\Procuration\ProxyVoteReminder",
 *     "request_administrative": "App\Entity\Procuration\RequestAdministrativeReminder",
 * })
 */
abstract class AbstractProcurationReminder
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="datetime")
     */
    private ?\DateTimeInterface $processedAt;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ProcurationRequest")
     * @ORM\JoinColumn(nullable=false)
     */
    private ProcurationRequest $procurationRequest;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ElectionRound")
     * @ORM\JoinColumn(nullable=false)
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

    public function getProcessedAt()
    {
        return $this->processedAt;
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
