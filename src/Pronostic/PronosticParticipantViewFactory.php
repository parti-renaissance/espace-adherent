<?php

declare(strict_types=1);

namespace App\Pronostic;

use App\Entity\Pronostic\PronosticParticipation;

class PronosticParticipantViewFactory
{
    public function create(PronosticParticipation $participation): array
    {
        return [
            'uuid' => $participation->getUuid()->toRfc4122(),
            'user' => [
                'uuid' => $participation->adherent->getUuid()->toRfc4122(),
                'first_name' => $participation->adherent->getFirstName(),
                'last_name' => $participation->adherent->getLastName(),
            ],
            'team_1_score' => $participation->team1Score,
            'team_2_score' => $participation->team2Score,
            'result_status' => $participation->getResultStatusCode(),
            'participated_at' => $participation->getCreatedAt()->format(\DateTimeInterface::ATOM),
        ];
    }
}
