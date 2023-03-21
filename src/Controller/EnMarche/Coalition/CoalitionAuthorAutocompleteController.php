<?php

namespace App\Controller\EnMarche\Coalition;

use App\Entity\Adherent;
use App\Repository\AdherentRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @IsGranted("ROLE_COALITION_MODERATOR")
 */
#[Route(path: '/espace-coalition/author/autocompletion', name: 'app_coalition_author_autocomplete', condition: 'request.isXmlHttpRequest()', methods: ['GET'])]
class CoalitionAuthorAutocompleteController extends AbstractController
{
    public function __invoke(Request $request, AdherentRepository $adherentRepository): JsonResponse
    {
        if (!$search = $request->query->get('search')) {
            return new JsonResponse([], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse(array_map(function (Adherent $adherent) {
            return [
                'uuid' => $adherent->getUuid()->toString(),
                'first_name' => $adherent->getFirstName(),
                'last_name' => $adherent->getLastName(),
                'registered_at' => $adherent->getRegisteredAt()->format('d/m/Y'),
                'is_adherent' => $adherent->isAdherent(),
                'is_female' => $adherent->isFemale(),
            ];
        }, $adherentRepository->findEnabledCoalitionUsers($search)));
    }
}
