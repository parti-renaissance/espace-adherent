<?php

declare(strict_types=1);

namespace App\Controller\Api\Vox;

use App\Entity\Adherent;
use App\Entity\Pronostic\Pronostic;
use App\Entity\Pronostic\PronosticParticipation;
use App\Pronostic\Request\CreatePronosticParticipationRequest;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/v3/pronostics/{uuid}/participants', name: 'api_v3_pronostic_participation_create', requirements: ['uuid' => '%pattern_uuid%'], methods: ['POST'])]
class CreatePronosticParticipationController extends AbstractController
{
    public function __invoke(
        #[MapEntity(mapping: ['uuid' => 'uuid'])]
        Pronostic $pronostic,
        #[CurrentUser] Adherent $user,
        #[MapRequestPayload]
        CreatePronosticParticipationRequest $payload,
        EntityManagerInterface $entityManager,
    ): Response {
        $now = new \DateTimeImmutable();
        if (!$pronostic->isOpenAt($now)) {
            $message = $now < $pronostic->beginAt
                ? 'Les participations à ce pronostic ne sont pas encore ouvertes.'
                : 'Les participations à ce pronostic sont fermées.';

            return $this->json(['message' => $message], Response::HTTP_CONFLICT);
        }

        $participation = new PronosticParticipation($pronostic, $user, (int) $payload->team1Score, (int) $payload->team2Score);
        $entityManager->persist($participation);

        try {
            $entityManager->flush();
        } catch (UniqueConstraintViolationException) {
            return $this->json(['message' => 'Vous avez déjà participé à ce pronostic.'], Response::HTTP_CONFLICT);
        }

        return new Response('', Response::HTTP_CREATED);
    }
}
