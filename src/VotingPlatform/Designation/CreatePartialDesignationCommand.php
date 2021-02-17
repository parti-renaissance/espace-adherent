<?php

namespace App\VotingPlatform\Designation;

use App\Entity\Committee;
use App\Validator\CommitteePartialDesignation as AssertCommitteePartialDesignation;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @AssertCommitteePartialDesignation
 */
class CreatePartialDesignationCommand
{
    /**
     * @var Committee
     *
     * @Assert\NotBlank
     */
    private $committee;

    /**
     * @var string
     *
     * @Assert\NotBlank
     * @Assert\Choice(callback={"App\VotingPlatform\Designation\DesignationTypeEnum", "toArray"}, message="Le type d'élection est invalide")
     */
    private $designationType;

    /**
     * @var string|null
     *
     * @Assert\Choice(callback={"App\Entity\VotingPlatform\ElectionPoolCodeEnum", "toArray"})
     */
    private $pool;

    /**
     * @var \DateTime|null
     *
     * @Assert\NotBlank
     * @Assert\DateTime
     */
    private $voteStartDate;

    /**
     * @var \DateTime|null
     *
     * @Assert\NotBlank
     * @Assert\DateTime
     */
    private $voteEndDate;

    /**
     * @var string|null
     *
     * @Assert\NotBlank
     * @Assert\Length(max=5000)
     */
    private $message;

    public function __construct(Committee $committee, string $designationType, ?string $pool)
    {
        $this->committee = $committee;
        $this->designationType = $designationType;
        $this->pool = $pool;
    }

    public function getCommittee(): Committee
    {
        return $this->committee;
    }

    public function getDesignationType(): string
    {
        return $this->designationType;
    }

    public function getPool(): ?string
    {
        return $this->pool;
    }

    public function getVoteStartDate(): ?\DateTime
    {
        return $this->voteStartDate;
    }

    public function setVoteStartDate(?\DateTime $voteStartDate): void
    {
        $this->voteStartDate = $voteStartDate;
    }

    public function getVoteEndDate(): ?\DateTime
    {
        return $this->voteEndDate;
    }

    public function setVoteEndDate(?\DateTime $voteEndDate): void
    {
        $this->voteEndDate = $voteEndDate;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): void
    {
        $this->message = $message;
    }
}
