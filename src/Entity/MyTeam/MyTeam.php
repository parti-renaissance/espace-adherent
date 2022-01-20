<?php

namespace App\Entity\MyTeam;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\Adherent;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     attributes={
 *         "normalization_context": {
 *             "groups": {"my_team_read"}
 *         },
 *         "access_control": "is_granted('ROLE_OAUTH_SCOPE_JEMENGAGE_ADMIN') and is_granted('IS_FEATURE_GRANTED', 'my_team')"
 *     },
 *     collectionOperations={
 *         "post": {
 *             "defaults": {"_api_receive": false},
 *             "path": "/v3/my_teams",
 *             "controller": "App\Controller\Api\MyTeam\InitializeMyTeamController"
 *         }
 *     }
 * )
 *
 * @ORM\Entity(repositoryClass="App\Repository\MyTeam\MyTeamRepository")
 */
class MyTeam
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Adherent")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     *
     * @Assert\NotNull
     */
    private Adherent $owner;

    /**
     * @ORM\Column
     *
     * @Assert\NotBlank
     * @Assert\Choice(choices=App\Scope\ScopeEnum::ALL)
     */
    private string $scope;

    /**
     * @var Member[]|Collection
     *
     * @ORM\OneToMany(
     *     targetEntity="App\Entity\MyTeam\Member",
     *     mappedBy="team",
     *     cascade={"all"},
     *     orphanRemoval=true
     * )
     */
    private Collection $members;

    public function __construct(Adherent $owner, string $scope, UuidInterface $uuid = null)
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
