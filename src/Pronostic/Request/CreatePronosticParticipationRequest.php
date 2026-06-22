<?php

declare(strict_types=1);

namespace App\Pronostic\Request;

use App\Entity\Pronostic\PronosticParticipation;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

class CreatePronosticParticipationRequest
{
    #[Assert\NotNull]
    #[Assert\Range(min: 0, max: PronosticParticipation::MAX_SCORE)]
    #[SerializedName('team_1_score')]
    public ?int $team1Score = null;

    #[Assert\NotNull]
    #[Assert\Range(min: 0, max: PronosticParticipation::MAX_SCORE)]
    #[SerializedName('team_2_score')]
    public ?int $team2Score = null;
}
