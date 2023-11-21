<?php

namespace App\Entity\TerritorialCouncil;

use App\AdherentMessage\StaticSegmentInterface;
use App\Collection\TerritorialCouncilMembershipCollection;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityReferentTagTrait;
use App\Entity\EntityZoneTrait;
use App\Entity\ReferentTag;
use App\Entity\StaticSegmentTrait;
use App\Entity\VotingPlatform\Designation\ElectionEntityInterface;
use App\Entity\VotingPlatform\Designation\EntityElectionHelperTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TerritorialCouncil\TerritorialCouncilRepository")
 */
#[UniqueEntity('name')]
class TerritorialCouncil implements StaticSegmentInterface, InstanceEntityInterface
{
    use EntityIdentityTrait;
    use EntityReferentTagTrait;
    use EntityElectionHelperTrait;
    use EntityZoneTrait;
    use StaticSegmentTrait;

    /**
     * @ORM\Column(unique=true)
     */
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private $name;

    /**
     * @ORM\Column(length=50, unique=true)
     */
    #[Assert\NotBlank]
    #[Assert\Length(max: 50)]
    private $codes;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": true})
     */
    private $isActive;

    /**
     * @var Collection|ReferentTag[]
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\ReferentTag", cascade={"persist"})
     * @ORM\JoinTable(
     *     name="territorial_council_referent_tag",
     *     joinColumns={
     *         @ORM\JoinColumn(name="territorial_council_id", referencedColumnName="id", onDelete="CASCADE")
     *     },
     *     inverseJoinColumns={
     *         @ORM\JoinColumn(name="referent_tag_id", referencedColumnName="id", onDelete="CASCADE")
     *     }
     * )
     */
    protected $referentTags;

    /**
     * @var Collection|TerritorialCouncilMembership[]
     *
     * @ORM\OneToMany(
     *     targetEntity="App\Entity\TerritorialCouncil\TerritorialCouncilMembership",
     *     cascade={"persist", "remove"},
     *     mappedBy="territorialCouncil",
     *     orphanRemoval=true
     * )
     */
    private $memberships;

    /**
     * @var Election[]|Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\TerritorialCouncil\Election", mappedBy="territorialCouncil", cascade={"all"}, orphanRemoval=true)
     */
    private $elections;

    /**
     * @var PoliticalCommittee|null
     *
     * @ORM\OneToOne(targetEntity="App\Entity\TerritorialCouncil\PoliticalCommittee", mappedBy="territorialCouncil", cascade={"all"}, orphanRemoval=true)
     */
    private $politicalCommittee;

    public function __construct(string $name = null, string $codes = null, bool $isActive = true)
    {
        $this->uuid = Uuid::uuid4();
        $this->name = $name;
        $this->codes = $codes;
        $this->isActive = $isActive;

        $this->referentTags = new ArrayCollection();
        $this->memberships = new ArrayCollection();
        $this->elections = new ArrayCollection();
    }

    public function getUuidToString(): string
    {
        return $this->uuid->toString();
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getCodes(): ?string
    {
        return $this->codes;
    }

    public function setCodes(string $codes): void
    {
        $this->codes = $codes;
    }

    public function getNameCodes(): string
    {
        return sprintf('%s (%s)', $this->name, $this->getCodes());
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): void
    {
        $this->isActive = $isActive;
    }

    public function getMemberships(): TerritorialCouncilMembershipCollection
    {
        if ($this->memberships instanceof TerritorialCouncilMembershipCollection) {
            return $this->memberships;
        }

        return new TerritorialCouncilMembershipCollection($this->memberships->toArray());
    }

    public function addMembership(TerritorialCouncilMembership $memberships): void
    {
        if (!$this->memberships->contains($memberships)) {
            $this->memberships->add($memberships);
        }
    }

    public function removeMembership(TerritorialCouncilMembership $memberships): void
    {
        $this->memberships->removeElement($memberships);
    }

    public function getMembershipsCount(): int
    {
        return $this->memberships->count();
    }

    public function addElection(ElectionEntityInterface $election): void
    {
        if (!$this->elections->contains($election)) {
            $election->setTerritorialCouncil($this);
            $this->elections->add($election);
        }
    }

    /**
     * @return ElectionEntityInterface[]
     */
    public function getElections(): array
    {
        return $this->elections->toArray();
    }

    public function getPoliticalCommittee(): ?PoliticalCommittee
    {
        return $this->politicalCommittee;
    }

    public function setPoliticalCommittee(PoliticalCommittee $politicalCommittee): void
    {
        $this->politicalCommittee = $politicalCommittee;
    }

    public function isPoliticalCommitteeActive(): bool
    {
        return $this->politicalCommittee ? $this->politicalCommittee->isActive() : false;
    }

    public function setIsPoliticalCommitteeActive(bool $isActive): void
    {
        if ($this->politicalCommittee) {
            $this->politicalCommittee->setIsActive($isActive);
        }
    }

    public function getPoliticalCommitteeMembershipsCount(): int
    {
        return $this->politicalCommittee->getMembershipsCount();
    }

    /**
     * Returns true if the council is located abroad
     */
    public function isFof(): bool
    {
        foreach ($this->referentTags as $tag) {
            if ($tag->isDistrictTag() && str_contains($tag->getCode(), 'CIRCO_FDE')) {
                return true;
            }
        }

        return false;
    }

    public function __toString(): string
    {
        return $this->getNameCodes();
    }
}
