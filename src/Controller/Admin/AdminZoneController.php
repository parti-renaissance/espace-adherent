<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Adherent\MandateTypeEnum;
use App\Repository\Geo\ZoneRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/zone')]
class AdminZoneController extends AbstractController
{
    private const MAX_SUGGESTIONS = 10;

    #[Route(path: '/autocompletion', name: 'app_admin_zone_autocomplete', condition: 'request.isXmlHttpRequest()', methods: ['GET'])]
    public function autocompletion(Request $request, ZoneRepository $repository): JsonResponse
    {
        $term = $request->query->get('zone');

        $mandateType = $request->query->get('type');
        $filter = MandateTypeEnum::ZONE_FILTER_BY_MANDATE[$mandateType] ?: [];

        $zones = $repository->findForMandateAdminAutocomplete(
            $term,
            $filter['types'] ?? [],
            $filter['codes'] ?? [],
            self::MAX_SUGGESTIONS
        );

        return $this->json(
            $zones,
            Response::HTTP_OK,
            [],
            ['groups' => ['autocomplete']]
        );
    }
}
