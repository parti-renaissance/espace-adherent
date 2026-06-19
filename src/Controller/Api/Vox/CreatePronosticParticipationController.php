<?php

declare(strict_types=1);

namespace App\Controller\Api\Vox;

use App\Entity\Adherent;
use App\Entity\Pronostic\PronosticParticipation;
use App\Pronostic\PronosticViewFactory;
use App\Repository\Pronostic\PronosticParticipationRepository;
use App\Repository\Pronostic\PronosticRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\JsonException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Uid\Uuid;

#[Route('/v3/pronostics/{uuid}/participants', name: 'api_v3_pronostic_participation_create', methods: ['POST'])]
class CreatePronosticParticipationController extends AbstractController
{
    public function __invoke(
        string $uuid,
        Request $request,
        #[CurrentUser] Adherent $user,
        PronosticRepository $pronosticRepository,
        PronosticParticipationRepository $participationRepository,
        PronosticViewFactory $viewFactory,
        EntityManagerInterface $entityManager,
    ): Response {
        if (!Uuid::isValid($uuid)) {
            throw $this->createNotFoundException('Pronostic introuvable.');
        }

        $pronostic = $pronosticRepository->findOneBy(['uuid' => Uuid::fromString($uuid)]);
        if (!$pronostic) {
            throw $this->createNotFoundException('Pronostic introuvable.');
        }

        $now = new \DateTimeImmutable();
        if (!$pronostic->isOpenAt($now)) {
            $message = $now < $pronostic->beginAt
                ? 'Les participations à ce pronostic ne sont pas encore ouvertes.'
                : 'Les participations à ce pronostic sont fermées.';

            return $this->json(['message' => $message], Response::HTTP_CONFLICT);
        }

        if ($participationRepository->findFor($pronostic, $user)) {
            return $this->json(['message' => 'Vous avez déjà participé à ce pronostic.'], Response::HTTP_CONFLICT);
        }

        try {
            $payload = $request->toArray();
        } catch (JsonException) {
            return $this->json(['message' => 'Le corps JSON est invalide.'], Response::HTTP_BAD_REQUEST);
        }

        $team1Score = $payload['team_1_score'] ?? null;
        $team2Score = $payload['team_2_score'] ?? null;

        if (!$this->isValidScore($team1Score) || !$this->isValidScore($team2Score)) {
            return $this->json(['message' => 'Les deux scores doivent être des entiers compris entre 0 et 10.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $participation = new PronosticParticipation($pronostic, $user, $team1Score, $team2Score);
        $entityManager->persist($participation);

        try {
            $entityManager->flush();
        } catch (UniqueConstraintViolationException) {
            return $this->json(['message' => 'Vous avez déjà participé à ce pronostic.'], Response::HTTP_CONFLICT);
        }

        return $this->json($viewFactory->create($pronostic, $participation, $now), Response::HTTP_CREATED);
    }

    private function isValidScore(mixed $score): bool
    {
        return \is_int($score) && $score >= 0 && $score <= PronosticParticipation::MAX_SCORE;
    }
}
