<?php

namespace App\Entity;

use App\Entity\VotingPlatform\Designation\BaseCandidacyInvitation;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CommitteeCandidacyInvitationRepository")
 */
class CommitteeCandidacyInvitation extends BaseCandidacyInvitation
{
    /**
     * @var CommitteeCandidacy
     *
     * @ORM\OneToOne(targetEntity="App\Entity\CommitteeCandidacy", mappedBy="invitation", cascade={"all"})
     */
    protected $candidacy;

    /**
     * @var CommitteeMembership
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\CommitteeMembership")
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     *
     * @Assert\NotBlank(groups={"Default", "invitation_edit"})
     */
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
