<?php

declare(strict_types=1);

namespace App\Controller\Api\Vox;

use App\Entity\Adherent;
use App\Pronostic\PronosticDataBuilder;
use App\Repository\Pronostic\PronosticParticipationRepository;
use App\Repository\Pronostic\PronosticRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class GetCurrentPronosticController extends AbstractController
{
    #[Route('/v3/pronostics/current', name: 'api_v3_pronostic_current', methods: ['GET'])]
    #[Route('/pronostics/current', name: 'api_pronostic_current_public', methods: ['GET'])]
    public function __invoke(
        Security $security,
        PronosticRepository $pronosticRepository,
        PronosticParticipationRepository $participationRepository,
        PronosticDataBuilder $dataBuilder,
    ): Response {
        $pronostic = $pronosticRepository->findLatest();

        if (null === $pronostic) {
            return new Response('', Response::HTTP_NO_CONTENT);
        }

        $user = $security->getUser();
        $participation = $user instanceof Adherent
            ? $participationRepository->findFor($pronostic, $user)
            : null;

        return $this->json(array_merge(
            $dataBuilder->build($pronostic, $participation, new \DateTimeImmutable()),
            ['image_url' => $dataBuilder->getImageUrl($pronostic)],
        ));
    }
}
