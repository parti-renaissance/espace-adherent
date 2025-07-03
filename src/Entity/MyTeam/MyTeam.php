<?php

namespace App\Entity\MyTeam;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\HttpOperation;
use App\Api\Filter\MyTeamScopeFilter;
use App\Controller\Api\MyTeam\InitializeMyTeamController;
use App\Entity\Adherent;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\Repository\MyTeam\MyTeamRepository;
use App\Scope\ScopeEnum;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/v3/my_teams',
            normalizationContext: ['groups' => ['my_team_read_list']]
        ),
        new HttpOperation(
            method: 'POST',
            uriTemplate: '/v3/my_teams',
            controller: InitializeMyTeamController::class,
            deserialize: false
        ),
    ],
    normalizationContext: ['groups' => ['my_team_read']],
    filters: [MyTeamScopeFilter::class],
    security: "is_granted('REQUEST_SCOPE_GRANTED', 'my_team')"
)]
#[ORM\Entity(repositoryClass: MyTeamRepository::class)]
class MyTeam
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;

    #[Assert\NotNull]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Adherent::class)]
    private Adherent $owner;

    #[Assert\Choice(choices: ScopeEnum::ALL)]
    #[Assert\NotBlank]
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
