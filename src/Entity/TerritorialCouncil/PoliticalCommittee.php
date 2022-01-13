<?php

namespace App\Entity\TerritorialCouncil;

use App\Entity\EntityIdentityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TerritorialCouncil\PoliticalCommitteeRepository")
 */
class PoliticalCommittee implements InstanceEntityInterface
{
    use EntityIdentityTrait;

    /**
     * @var TerritorialCouncil|null
     *
     * @ORM\OneToOne(targetEntity="App\Entity\TerritorialCouncil\TerritorialCouncil", cascade={"all"}, inversedBy="politicalCommittee")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $territorialCouncil;

    /**
     * @ORM\Column(unique=true)
     *
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     */
    private $name;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $isActive;

    /**
     * @var Collection|PoliticalCommitteeMembership[]
     *
     * @ORM\OneToMany(
     *     targetEntity="PoliticalCommitteeMembership",
     *     cascade={"persist", "remove"},
     *     mappedBy="politicalCommittee",
     *     orphanRemoval=true
     * )
     */
    private $memberships;

    public function __construct(string $name, TerritorialCouncil $territorialCouncil, bool $isActive = false)
    {
        $this->uuid = Uuid::uuid4();
        $this->name = $name;
        $this->territorialCouncil = $territorialCouncil;
        $this->isActive = $isActive;

        $this->memberships = new ArrayCollection();
    }

    public function getTerritorialCouncil(): TerritorialCouncil
    {
        return $this->territorialCouncil;
    }

    public function setTerritorialCouncil(TerritorialCouncil $territorialCouncil): void
    {
        $this->territorialCouncil = $territorialCouncil;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): void
    {
        $this->isActive = $isActive;
    }

    public function getMemberships(): Collection
    {
        return $this->memberships;
    }

    public function addMembership(PoliticalCommitteeMembership $memberships): void
    {
        if (!$this->memberships->contains($memberships)) {
            $this->memberships->add($memberships);
        }
    }

    public function removeMembership(PoliticalCommitteeMembership $memberships): void
    {
        $this->memberships->removeElement($memberships);
    }

    public function getMembershipsCount(): int
    {
        return $this->memberships->count();
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
