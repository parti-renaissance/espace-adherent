<?php

declare(strict_types=1);

namespace App\Controller\Api\Vox;

use App\Entity\Adherent;
use App\Pronostic\PronosticViewFactory;
use App\Repository\Pronostic\PronosticParticipationRepository;
use App\Repository\Pronostic\PronosticRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Uid\Uuid;

#[Route('/v3/pronostics/{uuid}', name: 'api_v3_pronostic', methods: ['GET'])]
class GetPronosticController extends AbstractController
{
    public function __invoke(
        string $uuid,
        #[CurrentUser] Adherent $user,
        PronosticRepository $pronosticRepository,
        PronosticParticipationRepository $participationRepository,
        PronosticViewFactory $viewFactory,
    ): Response {
        if (!Uuid::isValid($uuid)) {
            throw $this->createNotFoundException('Pronostic introuvable.');
        }

        $pronostic = $pronosticRepository->findOneBy(['uuid' => Uuid::fromString($uuid)]);
        if (!$pronostic) {
            throw $this->createNotFoundException('Pronostic introuvable.');
        }

        return $this->json($viewFactory->create(
            $pronostic,
            $participationRepository->findFor($pronostic, $user),
            new \DateTimeImmutable(),
        ));
    }
}
