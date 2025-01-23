<?php

namespace App\Controller\Api\Committee;

use App\Geo\Http\ZoneAutocompleteFilter;
use App\Repository\Geo\ZoneRepository;
use App\Scope\ScopeGeneratorResolver;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/v3/committees/used-zones', name: 'api_committee_get_used_zones', methods: ['GET'])]
#[Security("is_granted('REQUEST_SCOPE_GRANTED', 'committee')")]
class GetCommitteesZonesController extends AbstractController
{
    public function __invoke(ScopeGeneratorResolver $scopeGeneratorResolver, ZoneRepository $zoneRepository): Response
    {
        $scope = $scopeGeneratorResolver->generate();
        $zones = $scope->getZones();

        $filter = new ZoneAutocompleteFilter();
        $filter->searchEvenEmptyTerm = true;
        $filter->usedByCommittees = true;

        return $this->json(
            $zoneRepository->searchByFilterInsideManagedZones($filter, $zones, null),
            Response::HTTP_OK,
            [],
            ['groups' => ['zone:code,type']]
        );
    }
}
