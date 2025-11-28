<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\VotingPlatform\Designation\BaseCandidacyInvitation;
use App\Repository\CommitteeCandidacyInvitationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CommitteeCandidacyInvitationRepository::class)]
class CommitteeCandidacyInvitation extends BaseCandidacyInvitation
{
    /**
     * @var CommitteeCandidacy
     */
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: CommitteeCandidacy::class, inversedBy: 'invitations')]
    protected $candidacy;

    /**
     * @var CommitteeMembership
     */
    #[Assert\NotBlank(groups: ['Default', 'invitation_edit'])]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: CommitteeMembership::class)]
    protected $membership;

    public function getMembership(): ?CommitteeMembership
    {
        return $this->membership;
    }

    public function setMembership(?CommitteeMembership $membership): void
    {
        $this->membership = $membership;
    }
}
