<?php

declare(strict_types=1);

namespace App\Entity\Team;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Api\Filter\ScopeVisibilityFilter;
use App\Entity\Adherent;
use App\Entity\EntityAdherentBlameableInterface;
use App\Entity\EntityAdherentBlameableTrait;
use App\Entity\EntityAdministratorBlameableInterface;
use App\Entity\EntityAdministratorBlameableTrait;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityScopeVisibilityTrait;
use App\Entity\EntityScopeVisibilityWithZoneInterface;
use App\Entity\EntityTimestampableTrait;
use App\Entity\Geo\Zone;
use App\Repository\Team\TeamRepository;
use App\Validator\Scope\ScopeVisibility;
use App\Validator\UniqueInCollection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiFilter(filterClass: SearchFilter::class, properties: ['name' => 'partial', 'visibility' => 'exact'])]
#[ApiFilter(filterClass: ScopeVisibilityFilter::class)]
#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/v3/teams/{uuid}',
            requirements: ['uuid' => '%pattern_uuid%'],
            security: "is_granted('REQUEST_SCOPE_GRANTED', 'team') and is_granted('SCOPE_CAN_MANAGE', object)"
        ),
        new Put(
            uriTemplate: '/v3/teams/{uuid}',
            requirements: ['uuid' => '%pattern_uuid%'],
            security: "is_granted('REQUEST_SCOPE_GRANTED', 'team') and is_granted('SCOPE_CAN_MANAGE', object)"
        ),
        new GetCollection(
            uriTemplate: '/v3/teams',
            paginationMaximumItemsPerPage: 1000,
            normalizationContext: ['groups' => ['team_list_read']]
        ),
        new Post(uriTemplate: '/v3/teams'),
    ],
    normalizationContext: ['groups' => ['team_read']],
    denormalizationContext: ['groups' => ['team_write']],
    order: ['createdAt' => 'DESC'],
    security: "is_granted('REQUEST_SCOPE_GRANTED', 'team')"
)]
#[ORM\Entity(repositoryClass: TeamRepository::class)]
#[ORM\Table]
#[ORM\UniqueConstraint(columns: ['name', 'zone_id'])]
#[ScopeVisibility]
#[UniqueEntity(fields: ['name', 'zone'], message: 'team.name.already_exists', errorPath: 'name', ignoreNull: false)]
class Team implements \Stringable, EntityAdherentBlameableInterface, EntityAdministratorBlameableInterface, EntityScopeVisibilityWithZoneInterface
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use EntityAdministratorBlameableTrait;
    use EntityAdherentBlameableTrait;
    use EntityScopeVisibilityTrait;

    #[Assert\Length(min: 2, max: 255, minMessage: 'team.name.min_length', maxMessage: 'team.name.max_length')]
    #[Assert\NotBlank(message: 'team.name.not_blank')]
    #[Groups(['team_read', 'team_list_read', 'team_write', 'phoning_campaign_read', 'phoning_campaign_list'])]
    #[ORM\Column]
    private ?string $name;

    /**
     * @var Member[]|Collection
     */
    #[Assert\Valid]
    #[ORM\OneToMany(mappedBy: 'team', targetEntity: Member::class, cascade: ['all'], fetch: 'EXTRA_LAZY', orphanRemoval: true)]
    #[ORM\OrderBy(['createdAt' => 'DESC'])]
    #[UniqueInCollection(propertyPath: 'adherent', message: 'team.members.adherent_already_in_collection')]
    private Collection $members;

    #[Groups(['team_list_read'])]
    public ?bool $isDeletable = null;

    public function __construct(?UuidInterface $uuid = null, ?string $name = null, array $members = [], ?Zone $zone = null)
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

    #[Groups(['team_list_read', 'phoning_campaign_read', 'phoning_campaign_list'])]
    #[SerializedName('members_count')]
    public function getMembersCount(): int
    {
        return $this->members->count();
    }

    #[Groups(['team_read', 'team_list_read'])]
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
