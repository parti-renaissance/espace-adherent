<?php

namespace App\Controller\Api\Zone;

use App\AdherentSpace\AdherentSpaceEnum;
use App\Controller\EnMarche\AccessDelegatorTrait;
use App\Geo\ManagedZoneProvider;
use App\Repository\Geo\ZoneRepository;
use App\Scope\AuthorizationChecker;
use App\Scope\ScopeEnum;
use App\Scope\ScopeGeneratorResolver;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[IsGranted('REQUEST_SCOPE_GRANTED')]
#[Route(path: '/v3/zone/autocomplete', name: 'api_v3_zone_autocomplete_for_scope', methods: ['GET'])]
class ZoneAutocompleteRestrictedController extends AbstractZoneAutocompleteController
{
    use AccessDelegatorTrait;

    public function __invoke(
        Request $request,
        AuthorizationChecker $authorizationChecker,
        ZoneRepository $repository,
        ManagedZoneProvider $managedZoneProvider,
        ScopeGeneratorResolver $scopeGeneratorResolver,
    ): Response {
        $filter = $this->getFilter($request);
        $managedZones = [];

        if (!$filter->forMandateType) {
            if ($scope = $scopeGeneratorResolver->generate()) {
                $managedZones = $scope->getZones();
            } else {
                $scopeCode = $authorizationChecker->getScope($request);
                $user = $this->getMainUser($request->getSession());

                if (!\in_array($scopeCode, ScopeEnum::NATIONAL_SCOPES, true)) {
                    $managedZones = $managedZoneProvider->getManagedZones($user, AdherentSpaceEnum::SCOPES[$scopeCode]);
                }
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
