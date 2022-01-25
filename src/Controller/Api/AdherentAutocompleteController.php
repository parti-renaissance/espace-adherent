<?php

namespace App\Controller\Api;

use App\Repository\AdherentRepository;
use App\Scope\ScopeGeneratorResolver;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 *@Route("/v3/adherents/autocomplete", name="api_adherent_autocomplete", methods={"GET"})
 *
 * @Security("is_granted('IS_FEATURE_GRANTED', 'team') or is_granted('IS_FEATURE_GRANTED', 'my_team')")
 */
class AdherentAutocompleteController extends AbstractController
{
    public function __invoke(
        Request $request,
        AdherentRepository $repository,
        ScopeGeneratorResolver $scopeGeneratorResolver
    ): JsonResponse {
        $zones = [];
        if ($scope = $scopeGeneratorResolver->generate()) {
            $zones = $scope->getZones();
        }

        $query = $request->query->get('q');
        $maxResult = $request->query->getInt('max_result', 10);

        return $this->json(
            $repository->findAdherentByAutocompletion($query, $zones, $maxResult > 100 ? 100 : $maxResult),
            Response::HTTP_OK,
            [],
            ['groups' => ['adherent_autocomplete']]
        );
    }
}
