<?php

namespace App\Entity\MyTeam;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Api\Filter\MyTeamScopeFilter;
use App\Entity\Adherent;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\Repository\MyTeam\MyTeamRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     attributes={
 *         "filters": {MyTeamScopeFilter::class},
 *         "normalization_context": {
 *             "groups": {"my_team_read"}
 *         },
 *         "security": "is_granted('ROLE_OAUTH_SCOPE_JEMENGAGE_ADMIN') and is_granted('IS_FEATURE_GRANTED', 'my_team')"
 *     },
 *     collectionOperations={
 *         "get": {
 *             "path": "/v3/my_teams",
 *             "normalization_context": {
 *                 "groups": {"my_team_read_list"}
 *             },
 *         },
 *         "post": {
 *             "defaults": {"_api_receive": false},
 *             "path": "/v3/my_teams",
 *             "controller": "App\Controller\Api\MyTeam\InitializeMyTeamController"
 *         }
 *     }
 * )
 */
#[ORM\Entity(repositoryClass: MyTeamRepository::class)]
class MyTeam
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;

    /**
     * @Assert\NotNull
     */
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Adherent::class)]
    private Adherent $owner;

    /**
     * @Assert\NotBlank
     * @Assert\Choice(choices=App\Scope\ScopeEnum::ALL)
     */
    #[Groups(['my_team_read_list'])]
    #[ORM\Column]
    private string $scope;

    /**
     * @var Member[]|Collection
     */
    #[Groups(['my_team_read_list'])]
    #[ORM\OneToMany(mappedBy: 'team', targetEntity: Member::class, cascade: ['all'], orphanRemoval: true)]
    private Collection $members;

    public function __construct(Adherent $owner, string $scope, ?UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
        $this->owner = $owner;
        $this->scope = $scope;
        $this->members = new ArrayCollection();
    }

    public function getOwner(): Adherent
    {
        return $this->owner;
    }

    public function setOwner(Adherent $owner): void
    {
        $this->owner = $owner;
    }

    public function getScope(): string
    {
        return $this->scope;
    }

    public function setScope(string $scope): void
    {
        $this->scope = $scope;
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
}
