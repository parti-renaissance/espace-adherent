<?php

declare(strict_types=1);

namespace App\Entity\Pronostic;

use App\Entity\Adherent;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\Repository\Pronostic\PronosticParticipationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PronosticParticipationRepository::class)]
#[ORM\UniqueConstraint(name: 'uniq_pronostic_participation', columns: ['pronostic_id', 'adherent_id'])]
class PronosticParticipation implements \Stringable
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    public const int MAX_SCORE = 10;

    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Pronostic::class, inversedBy: 'participations')]
    public Pronostic $pronostic;

    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Adherent::class)]
    public Adherent $adherent;

    #[Assert\Range(min: 0, max: self::MAX_SCORE)]
    #[ORM\Column(type: 'smallint', options: ['unsigned' => true])]
    public int $team1Score;

    #[Assert\Range(min: 0, max: self::MAX_SCORE)]
    #[ORM\Column(type: 'smallint', options: ['unsigned' => true])]
    public int $team2Score;

    public function __construct(Pronostic $pronostic, Adherent $adherent, int $team1Score, int $team2Score)
    {
        $this->uuid = Uuid::v4();
        $this->pronostic = $pronostic;
        $this->adherent = $adherent;
        $this->team1Score = $team1Score;
        $this->team2Score = $team2Score;

        $pronostic->addParticipation($this);
    }

    public function __toString(): string
    {
        return \sprintf('%s - %s', $this->adherent->getFullName(), $this->pronostic);
    }

    public function getResultStatus(): string
    {
        return match ($this->getResultStatusCode()) {
            'won' => 'Gagné',
            'lost' => 'Perdu',
            'draw' => 'Match nul',
            default => 'En attente',
        };
    }

    public function getResultStatusCode(): string
    {
        if (!$this->pronostic->isResultPublished()) {
            return 'pending';
        }

        return $this->pronostic->getParticipationResultStatusCode($this);
    }
}
