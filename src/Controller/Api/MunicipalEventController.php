<?php

namespace App\Controller\Api;

use App\Repository\MunicipalEventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MunicipalEventController extends AbstractController
{
    /**
     * @Route("/municipal_events/categories", name="api_municipal_event_categories_list", methods={"GET"})
     */
    public function municipalEventCategoryList(Request $request, MunicipalEventRepository $repository): Response
    {
        if ($request->query->has('postal_code')) {
            return $this->json($repository->findCategoriesForPostalCode((array) $request->query->get('postal_code')));
        }

        return $this->json($repository->getAllCategories());
    }
}
