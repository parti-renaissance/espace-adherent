<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ElectionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ElectionRepository::class)]
#[ORM\Table(name: 'elections')]
#[UniqueEntity(fields: ['name'])]
class Election
{
    /**
     * @var int|null
     */
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Id]
    private $id;

    /**
     * @var string
     */
    #[Assert\Length(max: 255)]
    #[Assert\NotBlank]
    #[ORM\Column(unique: true)]
    private $name = '';

    /**
     * @var string
     */
    #[Assert\NotBlank]
    #[ORM\Column(type: 'text')]
    private $introduction = '';

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $proposalContent;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $requestContent;

    /**
     * @var ElectionRound[]|Collection
     */
    #[Assert\Count(min: 1, minMessage: 'election.rounds.min_count')]
    #[ORM\OneToMany(mappedBy: 'election', targetEntity: ElectionRound::class, cascade: ['all'], orphanRemoval: true)]
    private $rounds;

    public function __construct()
    {
        $this->rounds = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getIntroduction(): string
    {
        return $this->introduction;
    }

    public function setIntroduction(string $introduction): void
    {
        $this->introduction = $introduction;
    }

    public function getProposalContent(): ?string
    {
        return $this->proposalContent;
    }

    public function setProposalContent(?string $proposalContent): void
    {
        $this->proposalContent = $proposalContent;
    }

    public function getRequestContent(): ?string
    {
        return $this->requestContent;
    }

    public function setRequestContent(?string $requestContent): void
    {
        $this->requestContent = $requestContent;
    }

    /**
     * @return ElectionRound[]|Collection
     */
    public function getRounds(): Collection
    {
        return $this->rounds;
    }

    public function addRound(ElectionRound $round): void
    {
        if (!$this->rounds->contains($round)) {
            $this->rounds->add($round);
            $round->setElection($this);
        }
    }

    public function removeRound($round): void
    {
        $this->rounds->removeElement($round);
    }
}
