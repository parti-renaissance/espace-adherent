<?php

namespace App\Entity\TerritorialCouncil;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use App\Entity\Adherent;
use App\Entity\EntityIdentityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table
 * @ORM\Entity
 *
 * @UniqueEntity(fields={"adherent", "politicalCommittee"})
 *
 * @Algolia\Index(autoIndex=false)
 */
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
     * @ORM\Column(type="date")
     *
     * @Assert\NotNull
     */
    private $joinedAt;

    public function __construct(
        PoliticalCommittee $politicalCommittee,
        Adherent $adherent = null,
        \DateTime $joinedAt = null,
        UuidInterface $uuid = null
    ) {
        $this->uuid = $uuid ?? Uuid::uuid4();
        $this->politicalCommittee = $politicalCommittee;
        $this->adherent = $adherent;
        $this->joinedAt = $joinedAt ?? new \DateTime('now');

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
        return $this->getQualities()->filter(function (PoliticalCommitteeQuality $quality) use ($name) {
            return $quality->getName() === $name;
        })->count() > 0;
    }

    public function getJoinedAt(): \DateTime
    {
        return $this->joinedAt;
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
        return array_map(function (PoliticalCommitteeQuality $quality) {
            if (\in_array($quality->getName(), TerritorialCouncilQualityEnum::POLITICAL_COMMITTEE_MANAGED_IN_ADMIN_MEMBERS)) {
                return $quality->getName();
            }
        }, $this->qualities->toArray());
    }
}
