<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Repository\ElectedRepresentative\LabelNameRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AdminElectedRepresentativeController extends Controller
{
    /**
     * @Route("/elected-representative/labels/autocompletion",
     *     name="app_elected_representative_labels_autocomplete",
     *     condition="request.isXmlHttpRequest()",
     *     methods={"GET"}
     * )
     */
    public function labelsAutocompleteAction(Request $request, LabelNameRepository $labelNameRepository): JsonResponse
    {
        $labels = $labelNameRepository->findForAutocomplete(
            $request->query->get('term')
        );

        return new JsonResponse($labels);
    }
}
