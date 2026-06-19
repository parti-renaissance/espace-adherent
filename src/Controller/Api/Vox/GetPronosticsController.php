<?php

declare(strict_types=1);

namespace App\Controller\Api\Vox;

use App\Entity\Adherent;
use App\Entity\Pronostic\Pronostic;
use App\Pronostic\PronosticViewFactory;
use App\Repository\Pronostic\PronosticParticipationRepository;
use App\Repository\Pronostic\PronosticRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/v3/pronostics', name: 'api_v3__pronostics', methods: ['GET'])]
class GetPronosticsController extends AbstractController
{
    public function __invoke(
        #[CurrentUser] Adherent $user,
        PronosticRepository $pronosticRepository,
        PronosticParticipationRepository $participationRepository,
        PronosticViewFactory $viewFactory,
    ): Response {
        $now = new \DateTimeImmutable();
        $pronostics = $pronosticRepository->findAllOrdered();
        $participations = $participationRepository->findIndexedByPronostic($user, $pronostics);

        return $this->json(array_map(
            static fn (Pronostic $pronostic): array => $viewFactory->create(
                $pronostic,
                $participations[$pronostic->getId()] ?? null,
                $now,
            ),
            $pronostics,
        ));
    }
}
