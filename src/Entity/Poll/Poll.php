<?php

namespace App\Entity\Poll;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\Administrator;
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
 * @ApiResource(
 *     collectionOperations={
 *         "get": {
 *             "path": "/polls",
 *             "method": "GET",
 *         }
 *     },
 *     attributes={
 *         "normalization_context": {"groups": {"poll_read"}},
 *         "order": {"finishAt": "ASC"}
 *     }
 * )
 *
 * @ORM\Entity(repositoryClass="App\Repository\Poll\PollRepository")
 *
 * @ORM\Table(
 *     name="poll",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="poll_uuid_unique", columns="uuid")
 *     }
 * )
 */
class Poll
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;

    /**
     * @var string
     *
     * @ORM\Column
     *
     * @Assert\NotBlank(message="poll.question.not_blank")
     * @Assert\Length(
     *     min=2,
     *     max=255,
     *     minMessage="poll.question.min_length",
     *     maxMessage="poll.question.max_length"
     * )
     *
     * @SymfonySerializer\Groups({"poll_read"})
     */
    private $question;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(type="datetime")
     *
     * @Assert\NotNull(message="poll.finish_at.not_null")
     *
     * @SymfonySerializer\Groups({"poll_read"})
     */
    private $finishAt;

    /**
     * @var Choice[]|Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Poll\Choice", mappedBy="poll", cascade={"all"})
     */
    private $choices;

    /**
     * @var Administrator|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Administrator")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $createdBy;

    public function __construct(
        UuidInterface $uuid = null,
        string $question = null,
        \DateTimeInterface $finishAt = null,
        Administrator $createdBy = null
    ) {
        $this->uuid = $uuid ?: Uuid::uuid4();
        $this->question = $question;
        $this->finishAt = $finishAt;
        $this->createdBy = $createdBy;
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

    public function setFinishAt(\DateTimeInterface $finishAt): void
    {
        $this->finishAt = $finishAt;
    }

    public function getCreatedBy(): ?Administrator
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?Administrator $createdBy): void
    {
        $this->createdBy = $createdBy;
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

    /**
     * @SymfonySerializer\Groups({"poll_read"})
     */
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
            $result['choices'][$id]['percentage'] = $total !== 0
                ? round($choice['count'] / $total * 100, 1)
                : 0
            ;
        }

        return $result;
    }
}
