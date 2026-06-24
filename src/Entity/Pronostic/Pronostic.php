<?php

declare(strict_types=1);

namespace App\Entity\Pronostic;

use App\Entity\EntityAdministratorBlameableInterface;
use App\Entity\EntityAdministratorBlameableTrait;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\Entity\NotificationObjectInterface;
use App\Entity\UploadableFile;
use App\JeMengage\Push\Command\SendNotificationCommandInterface;
use App\Pronostic\PronosticReminderTypeEnum;
use App\Repository\Pronostic\PronosticRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: PronosticRepository::class)]
#[ORM\Index(fields: ['beginAt', 'matchAt'])]
class Pronostic implements \Stringable, EntityAdministratorBlameableInterface, NotificationObjectInterface
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use EntityAdministratorBlameableTrait;

    #[Assert\NotBlank]
    #[ORM\Column]
    public ?string $title = null;

    #[Assert\NotBlank]
    #[ORM\Column]
    public ?string $team1 = null;

    #[Assert\NotBlank]
    #[ORM\Column]
    public ?string $team2 = null;

    #[Assert\NotNull]
    #[Assert\PositiveOrZero]
    #[ORM\Column(type: 'smallint', options: ['unsigned' => true])]
    public ?int $gabrielTeam1Score = null;

    #[Assert\NotNull]
    #[Assert\PositiveOrZero]
    #[ORM\Column(type: 'smallint', options: ['unsigned' => true])]
    public ?int $gabrielTeam2Score = null;

    #[Assert\NotNull]
    #[ORM\Column(type: 'datetime_immutable')]
    public ?\DateTimeImmutable $beginAt = null;

    #[Assert\NotNull]
    #[ORM\Column(type: 'datetime_immutable')]
    public ?\DateTimeImmutable $matchAt = null;

    #[Assert\PositiveOrZero]
    #[ORM\Column(type: 'smallint', nullable: true, options: ['unsigned' => true])]
    public ?int $resultTeam1Score = null;

    #[Assert\PositiveOrZero]
    #[ORM\Column(type: 'smallint', nullable: true, options: ['unsigned' => true])]
    public ?int $resultTeam2Score = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    public ?\DateTimeImmutable $resultPublishedAt = null;

    #[ORM\Column(options: ['default' => false])]
    public bool $displayed = false;

    #[Assert\NotNull(
        message: 'Veuillez ajouter une image pour créer le pronostic.',
        groups: ['Admin_creation'])]
    #[ORM\JoinColumn]
    #[ORM\OneToOne(cascade: ['all'], orphanRemoval: true)]
    public ?UploadableFile $image = null;

    #[ORM\Column(options: ['default' => false])]
    public bool $creationNotified = false;

    #[ORM\Column(options: ['default' => false])]
    public bool $jMinus1Notified = false;

    #[ORM\Column(options: ['default' => false])]
    public bool $hMinus1Notified = false;

    #[ORM\Column(options: ['default' => false])]
    public bool $resultNotified = false;

    /** @var Collection<int, PronosticParticipation> */
    #[ORM\OneToMany(targetEntity: PronosticParticipation::class, mappedBy: 'pronostic', fetch: 'EXTRA_LAZY', orphanRemoval: true)]
    private Collection $participations;

    public function __construct()
    {
        $this->uuid = Uuid::v4();
        $this->participations = new ArrayCollection();
    }

    public function __toString(): string
    {
        return (string) $this->title;
    }

    /** @return Collection<int, PronosticParticipation> */
    public function getParticipations(): Collection
    {
        return $this->participations;
    }

    public function getParticipantsCount(): int
    {
        return $this->participations->count();
    }

    public function addParticipation(PronosticParticipation $participation): void
    {
        if (!$this->participations->contains($participation)) {
            $this->participations->add($participation);
        }
    }

    public function isResultPublished(): bool
    {
        return null !== $this->resultPublishedAt;
    }

    public function getPublishResult(): bool
    {
        return $this->isResultPublished();
    }

    public function setPublishResult(bool $publish): void
    {
        if ($publish && !$this->resultPublishedAt) {
            $this->resultPublishedAt = new \DateTimeImmutable();
        } elseif (!$publish) {
            $this->resultPublishedAt = null;
        }
    }

    public function isOpenAt(\DateTimeInterface $date): bool
    {
        return $this->beginAt <= $date && $date < $this->matchAt;
    }

    public function hasReminderBeenSent(PronosticReminderTypeEnum $type): bool
    {
        return match ($type) {
            PronosticReminderTypeEnum::CREATION => $this->creationNotified,
            PronosticReminderTypeEnum::J_MINUS_1 => $this->jMinus1Notified,
            PronosticReminderTypeEnum::H_MINUS_1 => $this->hMinus1Notified,
            PronosticReminderTypeEnum::RESULTS => $this->resultNotified,
        };
    }

    public function markReminderSent(PronosticReminderTypeEnum $type): void
    {
        match ($type) {
            PronosticReminderTypeEnum::CREATION => $this->creationNotified = true,
            PronosticReminderTypeEnum::J_MINUS_1 => $this->jMinus1Notified = true,
            PronosticReminderTypeEnum::H_MINUS_1 => $this->hMinus1Notified = true,
            PronosticReminderTypeEnum::RESULTS => $this->resultNotified = true,
        };
    }

    public function isNotificationEnabled(SendNotificationCommandInterface $command): bool
    {
        return true;
    }

    public function handleNotificationSent(SendNotificationCommandInterface $command): void
    {
    }

    public function isNational(): bool
    {
        return true;
    }

    public function isWonBy(PronosticParticipation $participation): bool
    {
        return $this->isResultPublished()
            && $this->resultTeam1Score === $participation->team1Score
            && $this->resultTeam2Score === $participation->team2Score;
    }

    #[Assert\Callback]
    public function validate(ExecutionContextInterface $context): void
    {
        if ($this->beginAt && $this->matchAt && $this->beginAt >= $this->matchAt) {
            $context->buildViolation('La date de début doit précéder la date du match.')
                ->atPath('beginAt')
                ->addViolation();
        }

        $hasTeam1Result = null !== $this->resultTeam1Score;
        $hasTeam2Result = null !== $this->resultTeam2Score;

        if ($hasTeam1Result !== $hasTeam2Result) {
            $context->buildViolation('Les deux scores du résultat doivent être renseignés ensemble.')
                ->atPath($hasTeam1Result ? 'resultTeam2Score' : 'resultTeam1Score')
                ->addViolation();
        }

        if ($this->resultPublishedAt && (!$hasTeam1Result || !$hasTeam2Result)) {
            $context->buildViolation('Le résultat doit être renseigné avant sa publication.')
                ->atPath('resultPublishedAt')
                ->addViolation();
        }
    }
}
