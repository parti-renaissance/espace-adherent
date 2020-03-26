<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Repository\ElectedRepresentative\ZoneRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminElectedRepresentativeController extends Controller
{
    /**
     * @Route("/elected-representative/zones/autocompletion",
     *     name="app_elected_representative_zone",
     *     condition="request.isXmlHttpRequest()",
     *     methods={"GET"}
     * )
     */
    public function zonesAutocompleteAction(Request $request, ZoneRepository $zoneRepository): JsonResponse
    {
        $zones = $zoneRepository->findForAutocomplete(
            $request->query->get('zone'),
            $request->query->get('type')
        );

        return $this->json(
            $zones,
            Response::HTTP_OK,
            [],
            ['groups' => ['autocomplete']]
        );
    }
}
