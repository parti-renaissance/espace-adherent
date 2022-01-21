<?php

namespace App\Entity\MyTeam;

use App\Entity\Adherent;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="my_team_member", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="team_member_unique", columns={"team_id", "adherent_id"}),
 * })
 *
 * @UniqueEntity(fields={"team", "adherent"}, errorPath="adherent", message="my_team.member.adherent.already_in_collection")
 */
class Member
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;

    /**
     * @ORM\ManyToOne(targetEntity=MyTeam::class, inversedBy="members")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private ?MyTeam $team = null;

    /**
     * @ORM\ManyToOne(targetEntity=Adherent::class, inversedBy="teamMemberships")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     *
     * @Assert\NotBlank(message="my_team.member.adherent.not_blank")
     */
    private ?Adherent $adherent = null;

    /**
     * @ORM\Column
     *
     * @Assert\NotBlank(message="my_team.member.role.not_blank")
     * @Assert\Choice(choices=App\MyTeam\RoleEnum::ALL, message="my_team.member.role.invalid_choice")
     */
    private ?string $role = null;

    /**
     * @ORM\Column(type="simple_array", nullable=true)
     *
     * @Assert\Choice(choices=App\Scope\FeatureEnum::ALL, multiple=true, message="my_team.member.scope_features.invalid_choice")
     */
    private array $scopeFeatures = [];

    public function __construct(
        Adherent $adherent = null,
        string $role = null,
        array $scopeFeatures = [],
        UuidInterface $uuid = null
    ) {
        $this->uuid = $uuid ?? Uuid::uuid4();
        $this->adherent = $adherent;
        $this->role = $role;
        $this->scopeFeatures = $scopeFeatures;
    }

    public function getTeam(): ?MyTeam
    {
        return $this->team;
    }

    public function setTeam(MyTeam $team): void
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

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): void
    {
        $this->role = $role;
    }

    public function getScopeFeatures(): array
    {
        return $this->scopeFeatures;
    }

    public function setScopeFeatures(array $scopeFeatures): void
    {
        $this->scopeFeatures = $scopeFeatures;
    }

    public function __toString(): string
    {
        return (string) $this->adherent;
    }
}
