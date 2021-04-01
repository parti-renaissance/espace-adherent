<?php

namespace App\Entity\Poll;

use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Poll\ChoiceRepository")
 *
 * @ORM\Table(
 *     name="poll_choice",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="poll_choice_uuid_unique", columns="uuid")
 *     }
 * )
 */
class Choice
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;

    public const YES = 'Oui';
    public const NO = 'Non';

    /**
     * @var string
     *
     * @ORM\Column
     *
     * @Assert\NotBlank(message="poll_choice.value.not_blank")
     * @Assert\Length(
     *     max=255,
     *     maxMessage="poll_choice.value.max_length"
     * )
     *
     * @SymfonySerializer\Groups({"poll_read"})
     */
    private $value;

    /**
     * @var Poll
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Poll\Poll", inversedBy="choices")
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     */
    private $poll;

    /**
     * @var Vote[]|Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Poll\Vote", mappedBy="choice", cascade={"all"})
     */
    private $votes;

    public function __construct(string $value = null, UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?: Uuid::uuid4();
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

    /**
     * @return Vote[]|Collection
     */
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
