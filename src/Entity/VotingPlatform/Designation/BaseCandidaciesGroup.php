<?php

declare(strict_types=1);

namespace App\Entity\VotingPlatform\Designation;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\MappedSuperclass]
abstract class BaseCandidaciesGroup implements \Countable
{
    /**
     * @var int|null
     */
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    private $id;

    /**
     * @var CandidacyInterface[]|Collection
     */
    protected $candidacies;

    public function __construct()
    {
        $this->candidacies = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function addCandidacy(CandidacyInterface $candidacy): void
    {
        if (!$this->candidacies->contains($candidacy)) {
            $candidacy->setCandidaciesGroup($this);
            $this->candidacies->add($candidacy);
        }
    }

    public function removeCandidacy(CandidacyInterface $candidacy): void
    {
        $this->candidacies->removeElement($candidacy);
        $candidacy->setCandidaciesGroup(null);
    }

    /**
     * @return CandidacyInterface[]
     */
    public function getCandidacies(): array
    {
        return $this->candidacies->toArray();
    }

    public function count(): int
    {
        return $this->candidacies->count();
    }
}
