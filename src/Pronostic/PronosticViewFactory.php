<?php

declare(strict_types=1);

namespace App\Pronostic;

use App\Entity\Pronostic\Pronostic;
use App\Entity\Pronostic\PronosticParticipation;

class PronosticViewFactory
{
    public function create(Pronostic $pronostic, ?PronosticParticipation $participation, \DateTimeInterface $now): array
    {
        $data = [
            'uuid' => $pronostic->getUuid()->toRfc4122(),
            'title' => $pronostic->title,
            'begin_at' => $pronostic->beginAt->format(\DateTimeInterface::ATOM),
            'match_at' => $pronostic->matchAt->format(\DateTimeInterface::ATOM),
            'team_1' => $pronostic->team1,
            'team_2' => $pronostic->team2,
            'gabriel_pronostic' => [
                'team_1_score' => $pronostic->gabrielTeam1Score,
                'team_2_score' => $pronostic->gabrielTeam2Score,
            ],
            'status' => $this->getStatus($pronostic, $participation, $now),
            'participation' => $participation ? [
                'team_1_score' => $participation->team1Score,
                'team_2_score' => $participation->team2Score,
            ] : null,
        ];

        if ($participation && $pronostic->isResultPublished()) {
            $data['result'] = [
                'team_1_score' => $pronostic->resultTeam1Score,
                'team_2_score' => $pronostic->resultTeam2Score,
            ];
            $data['won'] = $pronostic->isWonBy($participation);
        }

        return $data;
    }

    private function getStatus(Pronostic $pronostic, ?PronosticParticipation $participation, \DateTimeInterface $now): string
    {
        if ($participation && $pronostic->isResultPublished()) {
            return 'result_available';
        }

        if ($now < $pronostic->beginAt) {
            return 'scheduled';
        }

        if ($now >= $pronostic->matchAt) {
            return 'closed';
        }

        return $participation ? 'participated' : 'not_participated';
    }
}
