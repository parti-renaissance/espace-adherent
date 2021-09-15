<?php

namespace App\Entity\Team;

use App\Entity\Adherent;
use App\Entity\EntityAdministratorTrait;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\Team\TypeEnum;
use App\Validator\UniqueInCollection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Team\TeamRepository")
 * @ORM\Table(uniqueConstraints={
 *     @ORM\UniqueConstraint(name="team_type_name_unique", columns={"type", "name"}),
 * })
 *
 * @UniqueEntity(
 *     fields={"type", "name"},
 *     message="team.type_name.already_exists",
 *     errorPath="name"
 * )
 */
class Team
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use EntityAdministratorTrait;

    /**
     * @var string|null
     *
     * @ORM\Column(length=10)
     *
     * @Assert\NotBlank(message="team.type.not_blank")
     * @Assert\Choice(choices=App\Team\TypeEnum::ALL, message="team.type.choice")
     */
    private $type;

    /**
     * @var string|null
     *
     * @ORM\Column(length=255)
     *
     * @Assert\NotBlank(message="team.name.not_blank")
     * @Assert\Length(
     *     min=2,
     *     max=255,
     *     minMessage="team.name.min_length",
     *     maxMessage="team.name.max_length"
     * )
     */
    private $name;

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
     *
     * @Assert\Valid
     * @UniqueInCollection(propertyPath="adherent", message="team.members.adherent_already_in_collection")
     */
    private $members;

    public function __construct(
        UuidInterface $uuid = null,
        string $type = null,
        string $name = null,
        array $members = []
    ) {
        $this->uuid = $uuid ?? Uuid::uuid4();
        $this->type = $type;
        $this->name = $name;

        $this->members = new ArrayCollection();
        foreach ($members as $member) {
            $this->addMember($member);
        }
    }

    public function __toString(): string
    {
        return (string) $this->name;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): void
    {
        $this->type = $type;
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

    public function getMembersCount(): int
    {
        return $this->members->count();
    }

    public function isPhoning(): bool
    {
        return TypeEnum::PHONING === $this->type;
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
}
