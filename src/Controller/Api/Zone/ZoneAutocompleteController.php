<?php

namespace App\Controller\Api\Zone;

use App\AdherentSpace\AdherentSpaceEnum;
use App\Controller\EnMarche\AccessDelegatorTrait;
use App\Entity\Geo\Zone;
use App\Geo\ManagedZoneProvider;
use App\Repository\Geo\ZoneRepository;
use App\Scope\AuthorizationChecker;
use App\Scope\ScopeEnum;
use App\Scope\ScopeGeneratorResolver;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @Route("/v3/zone/autocomplete", name="api_v3_zone_autocomplete_for_scope", methods={"GET"})
 *
 * @Security("is_granted('REQUEST_SCOPE_GRANTED')")
 */
class ZoneAutocompleteController extends AbstractController
{
    use AccessDelegatorTrait;

    public const QUERY_SEARCH_PARAM = 'q';
    public const QUERY_ZONE_TYPE_PARAM = 'types';

    public function __invoke(
        Request $request,
        UserInterface $user,
        AuthorizationChecker $authorizationChecker,
        ZoneRepository $repository,
        ManagedZoneProvider $managedZoneProvider,
        ScopeGeneratorResolver $scopeGeneratorResolver
    ): Response {
        $term = (string) $request->query->get(self::QUERY_SEARCH_PARAM);
        $zoneTypes = Zone::TYPES;
        $managedZones = [];

        if (($zoneTypesFromRequest = $request->query->get(self::QUERY_ZONE_TYPE_PARAM)) && \is_array($zoneTypesFromRequest)) {
            $zoneTypes = $zoneTypesFromRequest;
        }

        if ($scope = $scopeGeneratorResolver->generate()) {
            $managedZones = $scope->getZones();
        } else {
            $scopeCode = $authorizationChecker->getScope($request);
            $user = $this->getMainUser($request->getSession());

            if (!\in_array($scopeCode, ScopeEnum::NATIONAL_SCOPES, true)) {
                $managedZones = $managedZoneProvider->getManagedZones($user, AdherentSpaceEnum::SCOPES[$scopeCode]);
            }
        }

        return $this->json($repository->searchByTermAndManagedZonesGroupedByType(
            $term,
            $managedZones,
            $zoneTypes,
            true,
            10
        ));
    }
}
