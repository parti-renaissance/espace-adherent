<?php

declare(strict_types=1);

namespace App\Controller\Api\Zone;

use App\Repository\Geo\ZoneRepository;
use App\Scope\AuthorizationChecker;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ZoneAutocompleteController extends AbstractZoneAutocompleteController
{
    public function __invoke(
        Request $request,
        AuthorizationChecker $authorizationChecker,
        ZoneRepository $repository,
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
