<?php

namespace App\Entity\Team;

use App\Entity\Adherent;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Team\MemberRepository")
 * @ORM\Table(uniqueConstraints={
 *     @ORM\UniqueConstraint(name="team_member_unique", columns={"team_id", "adherent_id"}),
 * })
 *
 * @UniqueEntity(
 *     fields={"team", "adherent"},
 *     message="team.member.already_exists",
 *     errorPath="adherent"
 * )
 */
class Member
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;

    /**
     * @var Team|null
     *
     * @ORM\ManyToOne(targetEntity=Team::class, inversedBy="members")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $team;

    /**
     * @var Adherent|null
     *
     * @ORM\ManyToOne(targetEntity=Adherent::class)
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     *
     * @Assert\NotBlank(message="team.member.adherent.not_blank")
     */
    private $adherent;

    public function __construct(UuidInterface $uuid = null, Adherent $adherent = null, Team $team = null)
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

    public function setTeam(?Team $team): void
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
