<?php

declare(strict_types=1);

namespace App\VotingPlatform\Designation;

use App\Entity\Committee;
use App\Entity\VotingPlatform\ElectionPoolCodeEnum;
use App\Validator\CommitteePartialDesignation as AssertCommitteePartialDesignation;
use App\Validator\DateRange as AssertDateRange;
use Symfony\Component\Validator\Constraints as Assert;

#[AssertCommitteePartialDesignation(groups: ['Strinct'])]
#[AssertDateRange(
    startDateField: 'voteStartDate',
    endDateField: 'voteEndDate',
    interval: '7 days|14 days',
    messageInterval: "Vous pouvez choisir d'ouvrir le vote dans 2 à 4 semaines et de le cloturer 7 à 14 jours plus tard."
)]
#[Assert\GroupSequence(['CreatePartialDesignationCommand', 'Strict'])]
class CreatePartialDesignationCommand
{
    /**
     * @var Committee
     */
    #[Assert\NotBlank]
    private $committee;

    /**
     * @var string
     */
    #[Assert\Choice(callback: [DesignationTypeEnum::class, 'toArray'], message: "Le type d'élection est invalide")]
    #[Assert\NotBlank]
    private $designationType;

    /**
     * @var string|null
     */
    #[Assert\Choice(callback: [ElectionPoolCodeEnum::class, 'toArray'])]
    private $pool;

    /**
     * @var \DateTime|null
     */
    #[Assert\NotBlank]
    private $voteStartDate;

    /**
     * @var \DateTime|null
     */
    #[Assert\NotBlank]
    private $voteEndDate;

    /**
     * @var string|null
     */
    #[Assert\Length(max: 2000, maxMessage: "Oups, votre message semble plus long qu'il n'en a l'air. Essayez de le raccourcir pour continuer.")]
    #[Assert\NotBlank]
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
