<?php

declare(strict_types=1);

namespace App\Controller\Api\Zone;

use App\Repository\Geo\ZoneRepository;
use App\Scope\ScopeGeneratorResolver;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('REQUEST_SCOPE_GRANTED')]
#[Route(path: '/v3/zone/autocomplete', name: 'api_v3_zone_autocomplete_for_scope', methods: ['GET'])]
class ZoneAutocompleteRestrictedController extends AbstractZoneAutocompleteController
{
    public function __invoke(
        Request $request,
        ZoneRepository $repository,
        ScopeGeneratorResolver $scopeGeneratorResolver,
    ): Response {
        $filter = $this->getFilter($request);
        $managedZones = [];

        if (!$filter->forMandateType) {
            if ($scope = $scopeGeneratorResolver->generate()) {
                $managedZones = $scope->getZones();
                $filter->committeeUuids = $scope->getCommitteeUuids();
            }
        }

        return $this->json(
            $repository->searchByFilterInsideManagedZones(
                $filter,
                $managedZones,
                $request->query->has('noLimit') ? null : $request->query->getInt('itemsPerType', 10)
            ),
            Response::HTTP_OK,
            [],
            ['groups' => ['zone_read']]
        );
    }
}
