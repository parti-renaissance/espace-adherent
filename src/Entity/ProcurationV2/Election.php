<?php

namespace App\Entity\ProcurationV2;

use App\Entity\EntityAdministratorBlameableTrait;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\Repository\Procuration\ElectionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: 'procuration_v2_elections')]
#[ORM\Entity(repositoryClass: ElectionRepository::class)]
#[UniqueEntity(fields: ['name'])]
#[UniqueEntity(fields: ['slug'])]
class Election
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use EntityAdministratorBlameableTrait;

    #[ORM\Column(unique: true)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public ?string $name = null;

    #[ORM\Column(length: 100, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    public ?string $slug = null;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank]
    public ?string $requestTitle = null;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank]
    public ?string $requestDescription = null;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank]
    public ?string $requestConfirmation = null;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank]
    public ?string $requestLegal = null;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank]
    public ?string $proxyTitle = null;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank]
    public ?string $proxyDescription = null;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank]
    public ?string $proxyConfirmation = null;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank]
    public ?string $proxyLegal = null;

    /**
     * @var Round[]|Collection
     */
    #[ORM\OneToMany(mappedBy: 'election', targetEntity: Round::class, cascade: ['all'], orphanRemoval: true)]
    #[Assert\Valid]
    public Collection $rounds;

    public function __construct(?UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
        $this->rounds = new ArrayCollection();
    }

    public function __toString(): string
    {
        return (string) $this->name;
    }

    public function addRound(Round $round): void
    {
        if (!$this->rounds->contains($round)) {
            $round->election = $this;
            $this->rounds->add($round);
        }
    }

    public function removeRound(Round $round): void
    {
        $this->rounds->removeElement($round);
    }

    public function getUpcomingRound(): ?Round
    {
        $rounds = $this->rounds->toArray();

        usort($rounds, static function (Round $round1, Round $round2): int {
            return $round1->date <=> $round2->date;
        });

        return $rounds[0] ?? null;
    }
}
