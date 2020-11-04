<?php

namespace App\Controller\Admin;

use App\Entity\ElectedRepresentative\MandateTypeEnum;
use App\Entity\Geo\Zone;
use App\Repository\Geo\ZoneRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/zone")
 */
class AdminZoneController extends Controller
{
    private const MAX_SUGGESTIONS = 10;

    private const CORSICA_MANDATE_TYPE = 'CORSICA_ASSEMBLY_MEMBER';
    private const CORSICA_REGION_CODE = '94';

    /**
     * @Route("/autocompletion",
     *     name="app_admin_zone_autocomplete",
     *     condition="request.isXmlHttpRequest()",
     *     methods={"GET"}
     * )
     */
    public function autocompletion(Request $request, ZoneRepository $repository): JsonResponse
    {
        $term = $request->query->get('zone');

        $mandateType = $request->query->get('type');
        $zoneTypes = MandateTypeEnum::ZONES_BY_MANDATE[$mandateType] ?? [];
        $isCorsica = !$zoneTypes && self::CORSICA_MANDATE_TYPE === $mandateType;

        $zones = $isCorsica
            ? $repository->findBy(['type' => Zone::REGION, 'code' => self::CORSICA_REGION_CODE])
            : $repository->findForMandateAdminAutocomplete($term, $zoneTypes, self::MAX_SUGGESTIONS);

        return $this->json(
            $zones,
            Response::HTTP_OK,
            [],
            ['groups' => ['autocomplete']]
        );
    }
}
