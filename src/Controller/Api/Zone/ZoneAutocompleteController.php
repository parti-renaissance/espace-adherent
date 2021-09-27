<?php

namespace App\Controller\Api\Zone;

use App\AdherentSpace\AdherentSpaceEnum;
use App\Controller\EnMarche\AccessDelegatorTrait;
use App\Entity\Geo\Zone;
use App\Geo\ManagedZoneProvider;
use App\Repository\Geo\ZoneRepository;
use App\Scope\AuthorizationChecker;
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

    public function __invoke(
        Request $request,
        UserInterface $user,
        AuthorizationChecker $authorizationChecker,
        ZoneRepository $repository,
        ManagedZoneProvider $managedZoneProvider
    ): Response {
        $scope = $authorizationChecker->getScope($request);
        $term = (string) $request->query->get('q');

        $user = $this->getMainUser($request->getSession());
        $managedZones = $managedZoneProvider->getManagedZones($user, AdherentSpaceEnum::SCOPES[$scope]);

        return $this->json($repository->searchByTermAndManagedZonesGroupedByType(
            $term,
            $managedZones,
            Zone::TYPES,
            true,
            10
        ));
    }
}
