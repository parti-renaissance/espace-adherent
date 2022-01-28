<?php

namespace App\Entity\MyTeam;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\Adherent;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\Scope\FeatureEnum;
use App\Validator\MyTeamMember as AssertMemberValid;
use App\Validator\MyTeamMemberScopeFeatures as AssertScopeFeaturesValid;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MyTeam\MemberRepository")
 * @ORM\Table(name="my_team_member", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="team_member_unique", columns={"team_id", "adherent_id"}),
 * })
 *
 * @UniqueEntity(fields={"team", "adherent"}, errorPath="adherent", message="my_team.member.adherent.already_in_collection")
 *
 * @ApiResource(
 *     shortName="MyTeamMember",
 *     attributes={
 *         "normalization_context": {
 *             "iri": true,
 *             "groups": {"my_team_member_read"},
 *         },
 *         "denormalization_context": {
 *             "groups": {"my_team_member_write"}
 *         },
 *         "access_control": "is_granted('IS_FEATURE_GRANTED', 'my_team')"
 *     },
 *     itemOperations={
 *         "get": {
 *             "path": "/v3/my_team_members/{id}",
 *             "requirements": {"id": "%pattern_uuid%"},
 *         },
 *         "put": {
 *             "path": "/v3/my_team_members/{id}",
 *             "requirements": {"id": "%pattern_uuid%"},
 *         },
 *         "delete": {
 *             "path": "/v3/my_team_members/{id}",
 *             "requirements": {"id": "%pattern_uuid%"},
 *             "access_control": "object.getTeam().getOwner() == user and is_granted('IS_FEATURE_GRANTED', 'my_team')",
 *         }
 *     },
 *     collectionOperations={
 *         "post": {
 *             "path": "/v3/my_team_members",
 *             "denormalization_context": {"groups": {"my_team_member_write", "my_team_member_post"}}
 *         }
 *     }
 * )
 */
class Member
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;

    /**
     * @ORM\ManyToOne(targetEntity=MyTeam::class, inversedBy="members")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     *
     * @Groups({"my_team_member_read", "my_team_member_write"})
     */
    private ?MyTeam $team = null;

    /**
     * @ORM\ManyToOne(targetEntity=Adherent::class, inversedBy="teamMemberships")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     *
     * @Assert\NotBlank(message="my_team.member.adherent.not_blank")
     * @AssertMemberValid
     *
     * @Groups({"my_team_member_read", "my_team_member_write", "my_team_read_list"})
     */
    private ?Adherent $adherent = null;

    /**
     * @ORM\Column
     *
     * @Assert\NotBlank(message="my_team.member.role.not_blank")
     * @Assert\Choice(choices=App\MyTeam\RoleEnum::ALL, message="my_team.member.role.invalid_choice")
     *
     * @Groups({"my_team_member_read", "my_team_member_write", "my_team_read_list"})
     */
    private ?string $role = null;

    /**
     * @ORM\Column(type="simple_array", nullable=true)
     *
     * @Assert\Choice(
     *     choices=App\Scope\FeatureEnum::AVAILABLE_FOR_DELEGATED_ACCESSES,
     *     multiple=true,
     *     multipleMessage="my_team.member.scope_features.invalid_choice"
     * )
     * @AssertScopeFeaturesValid
     *
     * @Groups({"my_team_member_read", "my_team_member_write", "my_team_read_list"})
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

    public function getScopeFeaturesAsAccesses(): array
    {
        return array_map(function (string $feature) {
            return array_flip(FeatureEnum::DELEGATED_ACCESSES_MAPPING)[$feature];
        }, $this->scopeFeatures);
    }

    public function __toString(): string
    {
        return (string) $this->adherent;
    }
}
