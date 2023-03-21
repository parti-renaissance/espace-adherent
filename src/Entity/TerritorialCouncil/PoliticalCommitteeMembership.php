<?php

namespace App\Entity\TerritorialCouncil;

use App\Entity\Adherent;
use App\Entity\EntityIdentityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TerritorialCouncil\PoliticalCommitteeMembershipRepository")
 */
#[UniqueEntity(fields: ['adherent', 'politicalCommittee'])]
class PoliticalCommitteeMembership
{
    use EntityIdentityTrait;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Adherent", inversedBy="politicalCommitteeMembership")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $adherent;

    /**
     * @var PoliticalCommittee|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\TerritorialCouncil\PoliticalCommittee", inversedBy="memberships", fetch="EAGER")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $politicalCommittee;

    /**
     * @var Collection|PoliticalCommitteeQuality[]
     *
     * @ORM\OneToMany(
     *     targetEntity="App\Entity\TerritorialCouncil\PoliticalCommitteeQuality",
     *     cascade={"all"},
     *     mappedBy="politicalCommitteeMembership",
     *     orphanRemoval=true
     * )
     */
    private $qualities;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    #[Assert\NotNull]
    private $joinedAt;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $isAdditional;

    public function __construct(
        PoliticalCommittee $politicalCommittee,
        Adherent $adherent = null,
        \DateTime $joinedAt = null,
        UuidInterface $uuid = null,
        bool $isAdditional = false
    ) {
        $this->uuid = $uuid ?? Uuid::uuid4();
        $this->politicalCommittee = $politicalCommittee;
        $this->adherent = $adherent;
        $this->joinedAt = $joinedAt ?? new \DateTime('now');
        $this->isAdditional = $isAdditional;

        $this->qualities = new ArrayCollection();
    }

    public function getPoliticalCommittee(): PoliticalCommittee
    {
        return $this->politicalCommittee;
    }

    public function setPoliticalCommittee(PoliticalCommittee $politicalCommittee): void
    {
        $this->politicalCommittee = $politicalCommittee;
    }

    public function getAdherent(): Adherent
    {
        return $this->adherent;
    }

    public function setAdherent(Adherent $adherent): void
    {
        $this->adherent = $adherent;
    }

    /**
     * @return Collection|PoliticalCommitteeQuality[]
     */
    public function getQualities(): Collection
    {
        return $this->qualities;
    }

    /**
     * Add quality if it is not present yet.
     * Check of quality presence made by quality's name.
     */
    public function addQuality(PoliticalCommitteeQuality $quality): void
    {
        if (!$this->hasQuality($quality->getName())) {
            $quality->setPoliticalCommitteeMembership($this);
            $this->qualities->add($quality);
        }
    }

    public function removeQuality(PoliticalCommitteeQuality $quality): void
    {
        $this->qualities->removeElement($quality);
    }

    public function removeQualityWithName(string $name): void
    {
        $key = null;
        foreach ($this->getQualities() as $k => $quality) {
            if ($quality->getName() === $name) {
                $key = $k;

                break;
            }
        }

        $this->qualities->remove($key);
    }

    public function clearQualities(): void
    {
        $this->qualities->clear();
    }

    public function hasQuality(string $name): bool
    {
        return $this->hasOneOfQualities([$name]);
    }

    public function hasOneOfQualities(array $names): bool
    {
        if (0 === $this->qualities->count()) {
            return false;
        }

        $criteria = Criteria::create()
            ->where(Criteria::expr()->in('name', $names))
        ;

        return $this->qualities->matching($criteria)->count() > 0;
    }

    public function getJoinedAt(): \DateTime
    {
        return $this->joinedAt;
    }

    public function isAdditional(): bool
    {
        return $this->isAdditional;
    }

    public function setIsAdditional(bool $isAdditional): void
    {
        $this->isAdditional = $isAdditional;
    }

    public function revoke(): void
    {
        $this->adherent = null;
    }

    public function getQualityNames(): array
    {
        return array_map(function (PoliticalCommitteeQuality $quality) {
            return $quality->getName();
        }, $this->qualities->toArray());
    }

    public function getManagedInAdminQualityNames(): array
    {
        return array_filter(array_map(function (PoliticalCommitteeQuality $quality) {
            if (\in_array($quality->getName(), TerritorialCouncilQualityEnum::POLITICAL_COMMITTEE_MANAGED_IN_ADMIN_MEMBERS)) {
                return $quality->getName();
            }

            return null;
        }, $this->qualities->toArray()));
    }
}
