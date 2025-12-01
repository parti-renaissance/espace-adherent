<?php

declare(strict_types=1);

namespace App\Entity\Poll;

use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\DiscriminatorColumn(name: 'type', type: 'string')]
#[ORM\DiscriminatorMap([PollTypeEnum::LOCAL => LocalPoll::class, PollTypeEnum::NATIONAL => NationalPoll::class])]
#[ORM\Entity]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\Table(name: 'poll')]
abstract class Poll implements \Stringable
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;

    /**
     * @var string
     */
    #[Assert\Length(min: 2, max: 255, minMessage: 'poll.question.min_length', maxMessage: 'poll.question.max_length')]
    #[Assert\NotBlank(message: 'poll.question.not_blank')]
    #[Groups(['poll_read'])]
    #[ORM\Column]
    private $question;

    /**
     * @var \DateTimeInterface
     */
    #[Assert\NotNull(message: 'poll.finish_at.not_null')]
    #[Groups(['poll_read'])]
    #[ORM\Column(type: 'datetime')]
    private $finishAt;

    /**
     * @var Choice[]|Collection
     */
    #[ORM\OneToMany(mappedBy: 'poll', targetEntity: Choice::class, cascade: ['all'])]
    private $choices;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private $published;

    public function __construct(
        ?UuidInterface $uuid = null,
        ?string $question = null,
        ?\DateTimeInterface $finishAt = null,
        bool $published = false,
    ) {
        $this->uuid = $uuid ?: Uuid::uuid4();
        $this->question = $question;
        $this->finishAt = $finishAt;
        $this->published = $published;
        $this->choices = new ArrayCollection();
    }

    public function __toString(): string
    {
        return (string) $this->question;
    }

    public function getQuestion(): ?string
    {
        return $this->question;
    }

    public function setQuestion(string $question): void
    {
        $this->question = $question;
    }

    public function getFinishAt(): ?\DateTimeInterface
    {
        return $this->finishAt;
    }

    public function setFinishAt(?\DateTimeInterface $finishAt): void
    {
        $this->finishAt = $finishAt;
    }

    public function isPublished(): bool
    {
        return $this->published;
    }

    public function setPublished(bool $published): void
    {
        $this->published = $published;
    }

    /**
     * @return Choice[]|Collection
     */
    public function getChoices(): Collection
    {
        return $this->choices;
    }

    public function addChoice(Choice $choice): void
    {
        if (!$this->choices->contains($choice)) {
            $choice->setPoll($this);
            $this->choices->add($choice);
        }
    }

    public function removeChoice(Choice $choice): void
    {
        $this->choices->removeElement($choice);
    }

    public function hasVote(): bool
    {
        foreach ($this->getChoices() as $choice) {
            if ($choice->hasVote()) {
                return true;
            }
        }

        return false;
    }

    #[Groups(['poll_read'])]
    public function getResult(): array
    {
        $result = [
            'total' => 0,
            'choices' => [],
        ];

        foreach ($this->choices as $choice) {
            $count = $choice->getVotes()->count();
            $result['total'] += $count;

            $result['choices'][] = [
                'choice' => $choice,
                'count' => $count,
            ];
        }

        $total = $result['total'];

        foreach ($result['choices'] as $id => $choice) {
            $result['choices'][$id]['percentage'] = 0 !== $total
                ? round($choice['count'] / $total * 100, 1)
                : 0;
        }

        return $result;
    }

    public function equals(self $other): bool
    {
        return $this->uuid->equals($other->getUuid());
    }
}
