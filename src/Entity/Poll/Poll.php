<?php

declare(strict_types=1);

namespace App\Entity\Poll;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Entity\EntityAdministratorBlameableInterface;
use App\Entity\EntityAdministratorBlameableTrait;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\Entity\IndexableEntityInterface;
use App\Entity\NotificationObjectInterface;
use App\EntityListener\AlgoliaIndexListener;
use App\JeMengage\Push\Command\SendNotificationCommandInterface;
use App\Poll\Api\State\CreatePollVoteProcessor;
use App\Poll\Api\State\CurrentPollProvider;
use App\Poll\PollReminderTypeEnum;
use App\Poll\Request\CreatePollVoteRequest;
use App\Repository\Poll\PollRepository;
use App\Validator\Poll\PollDatesDoNotOverlap;
use App\Validator\Poll\PollVotedChoiceCannotBeRemoved;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/polls',
            paginationItemsPerPage: 20,
        ),
        new Get(
            uriTemplate: '/polls/current',
            name: 'api_v3_poll_current',
            provider: CurrentPollProvider::class,
        ),
        new Get(
            uriTemplate: '/polls/{uuid}',
            requirements: ['uuid' => '%pattern_uuid%'],
        ),
        new Post(
            uriTemplate: '/polls/{uuid}/reply',
            requirements: ['uuid' => '%pattern_uuid%'],
            status: Response::HTTP_CREATED,
            input: CreatePollVoteRequest::class,
            output: false,
            processor: CreatePollVoteProcessor::class,
        ),
    ],
    routePrefix: '/v3',
    normalizationContext: ['groups' => ['poll_read']],
)]
#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/polls/current',
            name: 'api_poll_current_public',
            provider: CurrentPollProvider::class,
        ),
        new Get(
            uriTemplate: '/polls/{uuid}',
            requirements: ['uuid' => '%pattern_uuid%'],
            name: 'api_poll_item_public',
        ),
    ],
    normalizationContext: ['groups' => ['poll_public_read']],
)]
#[ORM\Entity(repositoryClass: PollRepository::class)]
#[ORM\EntityListeners([AlgoliaIndexListener::class])]
#[PollDatesDoNotOverlap(payload: ['trusted_html' => true])]
#[PollVotedChoiceCannotBeRemoved]
class Poll implements \Stringable, EntityAdministratorBlameableInterface, IndexableEntityInterface, NotificationObjectInterface
{
    use EntityAdministratorBlameableTrait;
    use EntityIdentityTrait;
    use EntityTimestampableTrait;

    #[Assert\Length(min: 2, max: 255, minMessage: 'poll.question.min_length', maxMessage: 'poll.question.max_length')]
    #[Assert\NotBlank(message: 'poll.question.not_blank')]
    #[Groups(['poll_read', 'poll_public_read'])]
    #[ORM\Column]
    private ?string $question;

    #[Assert\NotNull(message: 'poll.start_at.not_null')]
    #[Groups(['poll_read', 'poll_public_read'])]
    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $startAt;

    #[Assert\Expression('!value or !this.getStartAt() or value > this.getStartAt()', message: 'poll.finish_at.greater_than_start_at')]
    #[Assert\NotNull(message: 'poll.finish_at.not_null')]
    #[Groups(['poll_read', 'poll_public_read'])]
    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $finishAt;

    #[Assert\Expression('!value or !this.getFinishAt() or value >= this.getFinishAt()', message: 'poll.result_display_end_at.greater_than_or_equal_finish_at')]
    #[Groups(['poll_read', 'poll_public_read'])]
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $resultDisplayEndAt;

    #[Assert\Length(max: 1000)]
    #[Groups(['poll_read', 'poll_public_read'])]
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description;

    #[Groups(['poll_read', 'poll_public_read'])]
    #[ORM\OneToMany(targetEntity: Choice::class, mappedBy: 'poll', cascade: ['persist'], orphanRemoval: true)]
    private Collection $choices;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $published;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private bool $alertEnabled;

    #[ORM\Column(options: ['default' => false])]
    private bool $launchNotified = false;

    #[ORM\Column(options: ['default' => false])]
    private bool $reminderH8Notified = false;

    #[ORM\Column(options: ['default' => false])]
    private bool $closingH1Notified = false;

    #[Assert\GreaterThanOrEqual(0)]
    #[ORM\Column(type: 'smallint', options: ['unsigned' => true, 'default' => 0])]
    private int $participantCountThreshold;

    #[Groups(['poll_read'])]
    #[ORM\Column(length: 32, enumType: PollResultDisplayModeEnum::class, options: ['default' => 'after_vote'])]
    private PollResultDisplayModeEnum $resultDisplayMode;

    #[ORM\OneToMany(targetEntity: Vote::class, mappedBy: 'poll', fetch: 'EXTRA_LAZY')]
    private Collection $votes;

    public function __construct(
        ?Uuid $uuid = null,
        ?string $question = null,
        ?\DateTimeImmutable $finishAt = null,
        bool $published = false,
        ?\DateTimeImmutable $startAt = null,
        ?\DateTimeImmutable $resultDisplayEndAt = null,
        ?string $description = null,
        int $participantCountThreshold = 0,
        PollResultDisplayModeEnum $resultDisplayMode = PollResultDisplayModeEnum::AFTER_VOTE,
        bool $alertEnabled = true,
    ) {
        $this->uuid = $uuid ?: Uuid::v4();
        $this->question = $question;
        $this->startAt = $startAt ?? new \DateTimeImmutable();
        $this->finishAt = $finishAt;
        $this->resultDisplayEndAt = $resultDisplayEndAt;
        $this->description = $description;
        $this->published = $published;
        $this->alertEnabled = $alertEnabled;
        $this->participantCountThreshold = $participantCountThreshold;
        $this->resultDisplayMode = $resultDisplayMode;
        $this->choices = new ArrayCollection();
        $this->votes = new ArrayCollection();
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

    public function getShortQuestion(int $maxLength = 70): string
    {
        $question = (string) $this->question;

        if (mb_strlen($question) <= $maxLength) {
            return $question;
        }

        return rtrim(mb_substr($question, 0, $maxLength)).'…';
    }

    public function getStartAt(): ?\DateTimeImmutable
    {
        return $this->startAt;
    }

    public function setStartAt(?\DateTimeImmutable $startAt): void
    {
        $this->startAt = $startAt;
    }

    public function getFinishAt(): ?\DateTimeImmutable
    {
        return $this->finishAt;
    }

    public function setFinishAt(?\DateTimeImmutable $finishAt): void
    {
        $this->finishAt = $finishAt;
    }

    public function getResultDisplayEndAt(): ?\DateTimeImmutable
    {
        return $this->resultDisplayEndAt ?? $this->finishAt;
    }

    public function setResultDisplayEndAt(?\DateTimeImmutable $resultDisplayEndAt): void
    {
        $this->resultDisplayEndAt = $resultDisplayEndAt;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function isPublished(): bool
    {
        return $this->published;
    }

    public function isIndexable(): bool
    {
        return $this->published;
    }

    public function setPublished(bool $published): void
    {
        $this->published = $published;
    }

    public function isAlertEnabled(): bool
    {
        return $this->alertEnabled;
    }

    public function setAlertEnabled(bool $alertEnabled): void
    {
        $this->alertEnabled = $alertEnabled;
    }

    public function isAlertDisabled(): bool
    {
        return !$this->alertEnabled;
    }

    public function setAlertDisabled(bool $alertDisabled): void
    {
        $this->alertEnabled = !$alertDisabled;
    }

    public function getParticipantCountThreshold(): int
    {
        return $this->participantCountThreshold;
    }

    public function setParticipantCountThreshold(int $participantCountThreshold): void
    {
        $this->participantCountThreshold = $participantCountThreshold;
    }

    public function getResultDisplayMode(): PollResultDisplayModeEnum
    {
        return $this->resultDisplayMode;
    }

    public function getResultDisplayModeLabel(): string
    {
        return $this->resultDisplayMode->getLabel();
    }

    public function setResultDisplayMode(PollResultDisplayModeEnum $resultDisplayMode): void
    {
        $this->resultDisplayMode = $resultDisplayMode;
    }

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
        foreach ($this->choices as $choice) {
            if ($choice->hasVote()) {
                return true;
            }
        }

        return false;
    }

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

    public function isNotificationEnabled(SendNotificationCommandInterface $command): bool
    {
        return $this->alertEnabled;
    }

    public function handleNotificationSent(SendNotificationCommandInterface $command): void
    {
    }

    public function isNational(): bool
    {
        return true;
    }

    public function hasReminderBeenSent(PollReminderTypeEnum $type): bool
    {
        return match ($type) {
            PollReminderTypeEnum::LAUNCH => $this->launchNotified,
            PollReminderTypeEnum::REMINDER_H8 => $this->reminderH8Notified,
            PollReminderTypeEnum::CLOSING_H1 => $this->closingH1Notified,
        };
    }

    public function markReminderSent(PollReminderTypeEnum $type): void
    {
        match ($type) {
            PollReminderTypeEnum::LAUNCH => $this->launchNotified = true,
            PollReminderTypeEnum::REMINDER_H8 => $this->reminderH8Notified = true,
            PollReminderTypeEnum::CLOSING_H1 => $this->closingH1Notified = true,
        };
    }

    #[Groups(['poll_read', 'poll_public_read'])]
    public function getState(): PollStateEnum
    {
        $date = new \DateTimeImmutable();

        if (null !== $this->startAt && $date < $this->startAt) {
            return PollStateEnum::UPCOMING;
        }

        if (null !== $this->finishAt && $date >= $this->finishAt) {
            return PollStateEnum::FINISHED;
        }

        return PollStateEnum::IN_PROGRESS;
    }

    public function isVotePeriodActive(?\DateTimeInterface $date = null): bool
    {
        $date ??= new \DateTimeImmutable();

        return $this->published
            && null !== $this->startAt
            && null !== $this->finishAt
            && $this->startAt <= $date
            && $date < $this->finishAt;
    }

    public function canDisplayResult(?\DateTimeInterface $date = null, bool $hasVoted = false): bool
    {
        if (PollResultDisplayModeEnum::NEVER === $this->resultDisplayMode) {
            return false;
        }

        if (PollResultDisplayModeEnum::AFTER_VOTE === $this->resultDisplayMode) {
            return ($hasVoted && $this->isVisible($date)) || $this->isResultDisplayPeriodActive($date);
        }

        return $this->isResultDisplayPeriodActive($date);
    }

    public function canDisplayPercentage(bool $hasVoted): bool
    {
        return $hasVoted && PollResultDisplayModeEnum::NEVER !== $this->resultDisplayMode;
    }

    public function reachesParticipantCountThreshold(): bool
    {
        return $this->getResult()['total'] >= $this->participantCountThreshold;
    }

    public function exceedsParticipantCountThreshold(): bool
    {
        return $this->getResult()['total'] > $this->participantCountThreshold;
    }

    public function equals(self $other): bool
    {
        return $this->uuid->equals($other->getUuid());
    }

    private function isVisible(?\DateTimeInterface $date = null): bool
    {
        return $this->isVotePeriodActive($date) || $this->isResultDisplayPeriodActive($date);
    }

    private function isResultDisplayPeriodActive(?\DateTimeInterface $date = null): bool
    {
        $date ??= new \DateTimeImmutable();
        $resultDisplayEndAt = $this->getResultDisplayEndAt();

        return $this->published
            && PollResultDisplayModeEnum::NEVER !== $this->resultDisplayMode
            && null !== $this->finishAt
            && null !== $resultDisplayEndAt
            && $this->finishAt <= $date
            && $date < $resultDisplayEndAt;
    }

    #[Groups(['poll_read'])]
    public function getParticipantCount(): int
    {
        return $this->votes->count();
    }
}
