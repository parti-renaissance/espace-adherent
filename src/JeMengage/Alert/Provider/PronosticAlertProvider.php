<?php

declare(strict_types=1);

namespace App\JeMengage\Alert\Provider;

use App\Entity\Adherent;
use App\JeMengage\Alert\Alert;
use App\JeMengage\Alert\AlertTypeEnum;
use App\Pronostic\PronosticDataBuilder;
use App\Repository\Pronostic\PronosticParticipationRepository;
use App\Repository\Pronostic\PronosticRepository;

readonly class PronosticAlertProvider implements AlertProviderInterface
{
    public function __construct(
        private PronosticRepository $pronosticRepository,
        private PronosticParticipationRepository $participationRepository,
        private PronosticDataBuilder $dataBuilder,
    ) {
    }

    public function getAlerts(Adherent $adherent): array
    {
        $now = new \DateTimeImmutable();
        if (!$pronostic = $this->pronosticRepository->findDisplayed()) {
            return [];
        }

        $participation = $this->participationRepository->findFor($pronostic, $adherent);

        if ($pronostic->isResultPublished()) {
            $label = 'Résultat du pronostic';
            $description = $participation
                ? ($pronostic->isWonBy($participation) ? 'Bravo, vous avez gagné !' : 'Votre pronostic est perdu.')
                : 'Les résultats sont disponibles.';
        } elseif ($participation) {
            $label = 'J’ai participé';
            $description = \sprintf('Votre pronostic : %s %d - %d %s', $pronostic->team1, $participation->team1Score, $participation->team2Score, $pronostic->team2);
        } elseif ($now >= $pronostic->matchAt) {
            $label = 'Pronostic terminé';
            $description = 'Les participations sont fermées.';
        } else {
            $label = 'Je n’ai pas encore participé';
            $description = 'Donnez votre pronostic avant le début du match.';
        }

        $alert = new Alert(
            AlertTypeEnum::PRONOSTIC,
            $label,
            $pronostic->title,
            $description,
            $participation || $pronostic->isResultPublished() || $now >= $pronostic->matchAt ? 'Voir' : 'Participer',
            '/pronostics/'.$pronostic->getUuid()->toRfc4122(),
            imageUrl: $this->dataBuilder->getImageUrl($pronostic),
            data: $this->dataBuilder->build($pronostic, $participation, $now),
        );
        $alert->date = $pronostic->matchAt;

        return [$alert];
    }
}
