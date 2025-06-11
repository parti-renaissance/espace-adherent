<?php

namespace App\Entity\ProcurationV2;

use App\Entity\EntityAdministratorBlameableInterface;
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

#[ORM\Entity(repositoryClass: ElectionRepository::class)]
#[ORM\Table(name: 'procuration_v2_elections')]
#[UniqueEntity(fields: ['name'])]
#[UniqueEntity(fields: ['slug'])]
class Election implements EntityAdministratorBlameableInterface
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use EntityAdministratorBlameableTrait;

    #[Assert\Length(max: 255)]
    #[Assert\NotBlank]
    #[ORM\Column(unique: true)]
    public ?string $name = null;

    #[Assert\Length(max: 100)]
    #[Assert\NotBlank]
    #[ORM\Column(length: 100, unique: true)]
    public ?string $slug = null;

    #[Assert\NotBlank]
    #[ORM\Column(type: 'text')]
    public ?string $requestTitle = null;

    #[Assert\NotBlank]
    #[ORM\Column(type: 'text')]
    public ?string $requestDescription = null;

    #[Assert\NotBlank]
    #[ORM\Column(type: 'text')]
    public ?string $requestConfirmation = null;

    #[Assert\NotBlank]
    #[ORM\Column(type: 'text')]
    public ?string $requestLegal = null;

    #[Assert\NotBlank]
    #[ORM\Column(type: 'text')]
    public ?string $proxyTitle = null;

    #[Assert\NotBlank]
    #[ORM\Column(type: 'text')]
    public ?string $proxyDescription = null;

    #[Assert\NotBlank]
    #[ORM\Column(type: 'text')]
    public ?string $proxyConfirmation = null;

    #[Assert\NotBlank]
    #[ORM\Column(type: 'text')]
    public ?string $proxyLegal = null;

    /**
     * @var Round[]|Collection
     */
    #[Assert\Valid]
    #[ORM\OneToMany(mappedBy: 'election', targetEntity: Round::class, cascade: ['all'], orphanRemoval: true)]
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
