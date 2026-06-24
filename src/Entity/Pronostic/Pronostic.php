<?php

declare(strict_types=1);

namespace App\Entity\Pronostic;

use App\Entity\EntityAdministratorBlameableInterface;
use App\Entity\EntityAdministratorBlameableTrait;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\Entity\UploadableFile;
use App\Repository\Pronostic\PronosticRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: PronosticRepository::class)]
#[ORM\Index(fields: ['beginAt', 'matchAt'])]
class Pronostic implements \Stringable, EntityAdministratorBlameableInterface
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

    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[ORM\OneToOne(cascade: ['all'], orphanRemoval: true)]
    public ?UploadableFile $image = null;

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
