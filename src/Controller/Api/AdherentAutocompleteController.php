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
 * @Security("is_granted('IS_FEATURE_GRANTED', 'team')")
 */
class AdherentAutocompleteController extends AbstractController
{
    public function __invoke(Request $request, AdherentRepository $repository): JsonResponse
    {
        $query = $request->query->get('q');
        $maxResult = $request->query->getInt('max_result', 10);

        return $this->json(
            $repository->findAdherentByAutocompletion($query, $maxResult > 100 ? 100 : $maxResult),
            Response::HTTP_OK,
            [],
            ['groups' => ['adherent_autocomplete']]
        );
    }
}
