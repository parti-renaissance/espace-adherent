<?php

declare(strict_types=1);

namespace App\Entity\VotingPlatform\Designation\CandidacyPool;

use App\Entity\EntityIdentityTrait;
use App\Entity\VotingPlatform\Designation\Designation;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table('designation_candidacy_pool')]
class CandidacyPool
{
    use EntityIdentityTrait;

    #[Assert\NotBlank]
    #[ORM\Column]
    public ?string $label = null;

    #[ORM\ManyToOne(targetEntity: Designation::class, cascade: ['persist'], fetch: 'EAGER', inversedBy: 'candidacyPools')]
    public ?Designation $designation = null;

    /**
     * @var CandidaciesGroup[]|Collection
     */
    #[Assert\Count(min: 1)]
    #[Assert\Valid]
    #[ORM\OneToMany(mappedBy: 'candidacyPool', targetEntity: CandidaciesGroup::class, cascade: ['persist'], fetch: 'EAGER')]
    private $candidaciesGroups;

    public function __construct(?UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
        $this->candidaciesGroups = new ArrayCollection();
    }

    /** @return CandidaciesGroup[] */
    public function getCandidaciesGroups(): array
    {
        return $this->candidaciesGroups->toArray();
    }

    public function addCandidaciesGroup(CandidaciesGroup $candidaciesGroup): void
    {
        if (!$this->candidaciesGroups->contains($candidaciesGroup)) {
            $candidaciesGroup->candidacyPool = $this;
            $this->candidaciesGroups->add($candidaciesGroup);
        }
    }

    public function removeCandidaciesGroup(CandidaciesGroup $candidaciesGroup): void
    {
        $this->candidaciesGroups->removeElement($candidaciesGroup);
    }

    public function __toString(): string
    {
        return (string) $this->label;
    }
}
