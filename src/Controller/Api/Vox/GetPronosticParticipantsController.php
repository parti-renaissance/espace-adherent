<?php

declare(strict_types=1);

namespace App\Controller\Api\Vox;

use App\Entity\Adherent;
use App\Entity\Pronostic\PronosticParticipation;
use App\Pronostic\PronosticParticipantViewFactory;
use App\Repository\Pronostic\PronosticParticipationRepository;
use App\Repository\Pronostic\PronosticRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Uid\Uuid;

#[Route('/v3/pronostics/{uuid}/participants', name: 'api_v3_pronostic_participants', methods: ['GET'])]
class GetPronosticParticipantsController extends AbstractController
{
    public function __invoke(
        string $uuid,
        #[CurrentUser] Adherent $user,
        PronosticRepository $pronosticRepository,
        PronosticParticipationRepository $participationRepository,
        PronosticParticipantViewFactory $viewFactory,
    ): Response {
        if (!Uuid::isValid($uuid)) {
            throw $this->createNotFoundException('Pronostic introuvable.');
        }

        $pronostic = $pronosticRepository->findOneBy(['uuid' => Uuid::fromString($uuid)]);
        if (!$pronostic) {
            throw $this->createNotFoundException('Pronostic introuvable.');
        }

        if (!$participationRepository->findFor($pronostic, $user)) {
            return $this->json(['message' => 'Vous devez participer pour voir les pronostics des autres participants.'], Response::HTTP_FORBIDDEN);
        }

        return $this->json(array_map(
            static fn (PronosticParticipation $participation): array => $viewFactory->create($participation),
            $participationRepository->findAllForPronostic($pronostic),
        ));
    }
}
