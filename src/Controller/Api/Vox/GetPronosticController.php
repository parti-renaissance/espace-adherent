<?php

declare(strict_types=1);

namespace App\Controller\Api\Vox;

use App\Entity\Adherent;
use App\Entity\Pronostic\Pronostic;
use App\Pronostic\PronosticDataBuilder;
use App\Repository\Pronostic\PronosticParticipationRepository;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/v3/pronostics/{uuid}', name: 'api_v3_pronostic_get', requirements: ['uuid' => '%pattern_uuid%'], methods: ['GET'])]
class GetPronosticController extends AbstractController
{
    public function __invoke(
        Pronostic $pronostic,
        #[CurrentUser] Adherent $user,
        PronosticParticipationRepository $participationRepository,
        PronosticDataBuilder $dataBuilder,
    ): JsonResponse {
        $participation = $participationRepository->findFor($pronostic, $user);

        return $this->json(array_merge(
            $dataBuilder->build($pronostic, $participation, new \DateTimeImmutable()),
            ['image_url' => $dataBuilder->getImageUrl($pronostic)],
        ));
    }
}
