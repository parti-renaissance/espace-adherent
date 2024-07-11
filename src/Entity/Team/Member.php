<?php

namespace App\Entity\Team;

use App\Entity\Adherent;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\Repository\Team\MemberRepository;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: 'team_member')]
#[ORM\UniqueConstraint(name: 'team_member_unique', columns: ['team_id', 'adherent_id'])]
#[ORM\Entity(repositoryClass: MemberRepository::class)]
class Member
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;

    /**
     * @var Team|null
     */
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Team::class, inversedBy: 'members')]
    private $team;

    /**
     * @var Adherent|null
     */
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Adherent::class, inversedBy: 'teamMemberships')]
    #[Assert\NotBlank(message: 'team.member.adherent.not_blank')]
    private $adherent;

    public function __construct(?UuidInterface $uuid = null, ?Adherent $adherent = null, ?Team $team = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
        $this->team = $team;
        $this->adherent = $adherent;
    }

    public function __toString(): string
    {
        return (string) $this->adherent;
    }

    public function getTeam(): ?Team
    {
        return $this->team;
    }

    public function setTeam(Team $team): void
    {
        $this->team = $team;
    }

    public function getAdherent(): ?Adherent
    {
        return $this->adherent;
    }

    public function setAdherent(?Adherent $adherent): void
    {
        $this->adherent = $adherent;
    }
}
