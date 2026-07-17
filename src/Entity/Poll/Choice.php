<?php

declare(strict_types=1);

namespace App\Entity\Poll;

use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\Repository\Poll\ChoiceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ChoiceRepository::class)]
#[ORM\Table(name: 'poll_choice')]
class Choice implements \Stringable
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;

    #[Assert\Length(max: 255, maxMessage: 'poll_choice.value.max_length')]
    #[Assert\NotBlank(message: 'poll_choice.value.not_blank')]
    #[Groups(['poll_read', 'poll_public_read'])]
    #[ORM\Column]
    private ?string $value;

    #[ORM\JoinColumn(nullable: false)]
    #[ORM\ManyToOne(targetEntity: Poll::class, inversedBy: 'choices')]
    private ?Poll $poll = null;

    #[ORM\OneToMany(targetEntity: Vote::class, mappedBy: 'choice', cascade: ['persist'])]
    private Collection $votes;

    public function __construct(?string $value = null, ?Uuid $uuid = null)
    {
        $this->uuid = $uuid ?: Uuid::v4();
        $this->value = $value;
        $this->votes = new ArrayCollection();
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(string $value): void
    {
        $this->value = $value;
    }

    public function getPoll(): ?Poll
    {
        return $this->poll;
    }

    public function setPoll(Poll $poll): void
    {
        $this->poll = $poll;
    }

    public function getVotes(): Collection
    {
        return $this->votes;
    }

    public function addVote(Vote $vote): void
    {
        if (!$this->votes->contains($vote)) {
            $this->votes->add($vote);
        }
    }

    public function hasVote(): bool
    {
        return !$this->getVotes()->isEmpty();
    }
}
