<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Repository\PoliticalLabelRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AdminElectedRepresentativeController extends Controller
{
    /**
     * @Route("/elected-representative/labels/autocompletion",
     *     name="app_political_labels_autocomplete",
     *     condition="request.isXmlHttpRequest()",
     *     methods={"GET"}
     * )
     */
    public function politicalLabelsAutocompleteAction(
        Request $request,
        PoliticalLabelRepository $politicalLabelRepository
    ): JsonResponse {
        $labels = $politicalLabelRepository->findForAutocomplete(
            $request->query->get('term')
        );

        return new JsonResponse($labels);
    }
}
