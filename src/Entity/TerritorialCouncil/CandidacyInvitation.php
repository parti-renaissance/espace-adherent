<?php

namespace App\Entity\TerritorialCouncil;

use App\Entity\VotingPlatform\Designation\BaseCandidacyInvitation;
use App\Validator\TerritorialCouncil\ValidTerritorialCouncilCandidacyForCopolInvitation;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ValidTerritorialCouncilCandidacyForCopolInvitation(groups={"copol_election"})
 */
#[ORM\Table(name: 'territorial_council_candidacy_invitation')]
#[ORM\Entity]
class CandidacyInvitation extends BaseCandidacyInvitation
{
    /**
     * @var Candidacy
     */
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Candidacy::class, inversedBy: 'invitations')]
    protected $candidacy;

    /**
     * @var TerritorialCouncilMembership
     *
     * @Assert\NotBlank(groups={"Default", "national_council_election", "copol_election"})
     */
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: TerritorialCouncilMembership::class)]
    protected $membership;

    public function getMembership(): ?TerritorialCouncilMembership
    {
        return $this->membership;
    }

    public function setMembership(?TerritorialCouncilMembership $membership): void
    {
        $this->membership = $membership;
    }
}
