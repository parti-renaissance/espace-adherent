<?php

declare(strict_types=1);

namespace App\JeMengage\Alert\Provider;

use App\Entity\Adherent;
use App\Entity\Pronostic\Pronostic;
use App\Entity\Pronostic\PronosticParticipation;
use App\JeMengage\Alert\Alert;
use App\JeMengage\Alert\AlertTypeEnum;
use App\Repository\Pronostic\PronosticParticipationRepository;
use App\Repository\Pronostic\PronosticRepository;

readonly class PronosticAlertProvider implements AlertProviderInterface
{
    public function __construct(
        private PronosticRepository $pronosticRepository,
        private PronosticParticipationRepository $participationRepository,
    ) {
    }

    public function getAlerts(Adherent $adherent): array
    {
        $now = new \DateTimeImmutable();
        if (!$pronostic = $this->pronosticRepository->findDisplayed()) {
            return [];
        }

        if ($now >= $pronostic->matchAt && !$pronostic->isResultPublished()) {
            return [];
        }

        $participation = $this->participationRepository->findFor($pronostic, $adherent);

        if ($pronostic->isResultPublished() && !$participation) {
            return [];
        }

        if ($pronostic->isResultPublished()) {
            $label = 'Résultat du pronostic';
            $description = $pronostic->isWonBy($participation) ? 'Bravo, vous avez gagné !' : 'Votre pronostic est perdu.';
        } elseif ($participation) {
            $label = 'J’ai participé';
            $description = \sprintf('Votre pronostic : %s %d - %d %s', $pronostic->team1, $participation->team1Score, $participation->team2Score, $pronostic->team2);
        } else {
            $label = 'Je n’ai pas encore participé';
            $description = 'Donnez votre pronostic avant le début du match.';
        }

        $alert = new Alert(
            AlertTypeEnum::PRONOSTIC,
            $label,
            $pronostic->title,
            $description,
            $participation || $pronostic->isResultPublished() ? 'Voir' : 'Participer',
            '/pronostics/'.$pronostic->getUuid()->toRfc4122(),
            data: $this->buildData($pronostic, $participation, $now),
        );
        $alert->date = $pronostic->matchAt;

        return [$alert];
    }

    private function buildData(Pronostic $pronostic, ?PronosticParticipation $participation, \DateTimeInterface $now): array
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
