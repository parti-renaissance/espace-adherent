<?php

declare(strict_types=1);

namespace App\JeMengage\Alert\Provider;

use App\Entity\Adherent;
use App\JeMengage\Alert\Alert;
use App\JeMengage\Alert\AlertTypeEnum;
use App\Pronostic\PronosticViewFactory;
use App\Repository\Pronostic\PronosticParticipationRepository;
use App\Repository\Pronostic\PronosticRepository;

readonly class PronosticAlertProvider implements AlertProviderInterface
{
    public function __construct(
        private PronosticRepository $pronosticRepository,
        private PronosticParticipationRepository $participationRepository,
        private PronosticViewFactory $viewFactory,
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

        $view = $this->viewFactory->create($pronostic, $participation, $now);

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
            data: $view,
        );
        $alert->date = $pronostic->matchAt;

        return [$alert];
    }
}
