<?php

namespace App\Controller\Api;

use App\Repository\AdherentRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 *@Route("/v3/adherents/autocomplete", name="api_adherent_autocomplete", methods={"GET"})
 *
 * @Security("is_granted('HAS_FEATURE_TEAM')")
 */
class AdherentAutocompleteController extends AbstractController
{
    private const MAX_RESULT = 100;

    public function __invoke(Request $request, AdherentRepository $repository): JsonResponse
    {
        $query = $request->query->get('q');

        return $this->json(
            $repository->findAdherentByAutocompletion($query, self::MAX_RESULT),
            Response::HTTP_OK,
            [],
            ['groups' => ['adherent_autocomplete']]
        );
    }
}
