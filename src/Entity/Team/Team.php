<?php

namespace App\Entity\Team;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Api\Filter\ScopeVisibilityFilter;
use App\Entity\Adherent;
use App\Entity\EntityAdherentBlameableInterface;
use App\Entity\EntityAdherentBlameableTrait;
use App\Entity\EntityAdministratorBlameableInterface;
use App\Entity\EntityAdministratorBlameableTrait;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityScopeVisibilityInterface;
use App\Entity\EntityScopeVisibilityTrait;
use App\Entity\EntityTimestampableTrait;
use App\Entity\Geo\Zone;
use App\Validator\Scope\ScopeVisibility;
use App\Validator\UniqueInCollection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     attributes={
 *         "order": {"createdAt": "DESC"},
 *         "normalization_context": {
 *             "groups": {"team_read"}
 *         },
 *         "denormalization_context": {
 *             "groups": {"team_write"}
 *         },
 *         "access_control": "is_granted('IS_FEATURE_GRANTED', 'team')"
 *     },
 *     collectionOperations={
 *         "get": {
 *             "path": "/v3/teams",
 *             "normalization_context": {
 *                 "groups": {"team_list_read"}
 *             }
 *         },
 *         "post": {
 *             "path": "/v3/teams",
 *         }
 *     },
 *     itemOperations={
 *         "get": {
 *             "path": "/v3/teams/{id}",
 *             "requirements": {"id": "%pattern_uuid%"}
 *         },
 *         "put": {
 *             "path": "/v3/teams/{id}",
 *             "requirements": {"id": "%pattern_uuid%"},
 *             "access_control": "is_granted('IS_FEATURE_GRANTED', 'team') and is_granted('SCOPE_CAN_EDIT', object)"
 *         }
 *     }
 * )
 *
 * @ApiFilter(SearchFilter::class, properties={
 *     "name": "partial",
 * })
 *
 * @ApiFilter(ScopeVisibilityFilter::class)
 *
 * @ORM\Entity(repositoryClass="App\Repository\Team\TeamRepository")
 * @ORM\Table(uniqueConstraints={
 *     @ORM\UniqueConstraint(name="team_name_unique", columns={"name"}),
 * })
 *
 * @UniqueEntity(
 *     fields={"name"},
 *     message="team.name.already_exists",
 *     errorPath="name"
 * )
 *
 * @ScopeVisibility
 */
class Team implements EntityAdherentBlameableInterface, EntityAdministratorBlameableInterface, EntityScopeVisibilityInterface
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use EntityAdministratorBlameableTrait;
    use EntityAdherentBlameableTrait;
    use EntityScopeVisibilityTrait;

    /**
     * @ORM\Column(length=255)
     *
     * @Assert\NotBlank(message="team.name.not_blank")
     * @Assert\Length(
     *     min=2,
     *     max=255,
     *     minMessage="team.name.min_length",
     *     maxMessage="team.name.max_length"
     * )
     *
     * @SymfonySerializer\Groups({"team_read", "team_list_read", "team_write", "phoning_campaign_read", "phoning_campaign_list"})
     */
    private ?string $name;

    /**
     * @var Member[]|Collection
     *
     * @ORM\OneToMany(
     *     targetEntity="App\Entity\Team\Member",
     *     mappedBy="team",
     *     cascade={"all"},
     *     orphanRemoval=true,
     *     fetch="EXTRA_LAZY"
     * )
     * @ORM\OrderBy({"createdAt": "DESC"})
     *
     * @Assert\Valid
     * @UniqueInCollection(propertyPath="adherent", message="team.members.adherent_already_in_collection")
     */
    private Collection $members;

    public function __construct(UuidInterface $uuid = null, string $name = null, array $members = [], Zone $zone = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
        $this->name = $name;

        $this->members = new ArrayCollection();
        foreach ($members as $member) {
            $this->addMember($member);
        }

        $this->setZone($zone);
    }

    public function __toString(): string
    {
        return (string) $this->name;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return Member[]|Collection
     */
    public function getMembers(): Collection
    {
        return $this->members;
    }

    public function addMember(Member $member): void
    {
        if (!$this->members->contains($member)) {
            $member->setTeam($this);
            $this->members->add($member);
        }
    }

    public function removeMember(Member $member): void
    {
        $this->members->removeElement($member);
    }

    /**
     * @SymfonySerializer\Groups({"team_list_read", "phoning_campaign_read", "phoning_campaign_list"})
     * @SymfonySerializer\SerializedName("members_count")
     */
    public function getMembersCount(): int
    {
        return $this->members->count();
    }

    /**
     * @SymfonySerializer\Groups({"team_read", "team_list_read"})
     */
    public function getCreator(): string
    {
        return null !== $this->createdByAdherent ? $this->createdByAdherent->getFullName() : 'Admin';
    }

    public function __clone()
    {
        $this->members = new ArrayCollection($this->members->toArray());
    }

    public function hasAdherent(Adherent $adherent): bool
    {
        foreach ($this->members as $member) {
            if ($member->getAdherent() === $adherent) {
                return true;
            }
        }

        return false;
    }

    public function getMember(Adherent $adherent): ?Member
    {
        foreach ($this->members as $member) {
            if ($member->getAdherent() === $adherent) {
                return $member;
            }
        }

        return null;
    }

    public function reorderMembersCollection(): void
    {
        $this->members = new ArrayCollection(array_values($this->members->matching(Criteria::create()->orderBy(['createdAt' => 'DESC']))->toArray()));
    }
}
