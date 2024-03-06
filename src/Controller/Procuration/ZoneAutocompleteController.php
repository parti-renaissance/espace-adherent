<?php

namespace App\Controller\Procuration;

use App\Controller\Api\Zone\AbstractZoneAutocompleteController;
use App\Controller\EnMarche\AccessDelegatorTrait;
use App\Geo\ManagedZoneProvider;
use App\Repository\Geo\ZoneRepository;
use App\Scope\AuthorizationChecker;
use App\Scope\ScopeGeneratorResolver;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/zone/autocomplete', name: 'api_procuration_zone_autocomplete', methods: ['GET'])]
class ZoneAutocompleteController extends AbstractZoneAutocompleteController
{
    use AccessDelegatorTrait;

    public function __invoke(
        Request $request,
        AuthorizationChecker $authorizationChecker,
        ZoneRepository $repository,
        ManagedZoneProvider $managedZoneProvider,
        ScopeGeneratorResolver $scopeGeneratorResolver
    ): Response {
        $filter = $this->getFilter($request);
        $managedZones = [];

        if (
            ($parentZoneUuid = $request->query->get('parent_zone'))
            && ($parentZone = $repository->findOneByUuid($parentZoneUuid))
        ) {
            $managedZones[] = $parentZone;
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
