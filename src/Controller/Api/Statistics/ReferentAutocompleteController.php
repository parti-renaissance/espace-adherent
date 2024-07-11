<?php

namespace App\Controller\Api\Statistics;

use App\Repository\CommitteeRepository;
use App\Repository\EventRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

#[IsGranted('ROLE_OAUTH_SCOPE_READ:STATS')]
#[Route(path: '/statistics/search/autocomplete', name: 'api_statistics_referent_space_search_autocomplete', methods: ['GET'])]
class ReferentAutocompleteController extends AbstractStatisticsController
{
    private const AUTOCOMPLETE_TYPE_CITY = 'city';
    private const AUTOCOMPLETE_TYPE_COMMITTEE = 'committee';
    private const AUTOCOMPLETE_TYPE_COUNTRY = 'country';

    private const AUTOCOMPLETE_TYPES = [
        self::AUTOCOMPLETE_TYPE_CITY,
        self::AUTOCOMPLETE_TYPE_COMMITTEE,
        self::AUTOCOMPLETE_TYPE_COUNTRY,
    ];

    public function __invoke(
        Request $request,
        CommitteeRepository $committeeRepository,
        EventRepository $eventRepository
    ): Response {
        $referent = $this->findReferent($request);

        // parameter `type` should have a value different from an empty string, but parameter `value` can be an empty string
        if (!($type = $request->query->get('type')) || !$request->query->has('value')) {
            throw new BadRequestHttpException('The parameters "type" and "value" are required.');
        }

        if (!\in_array($type, self::AUTOCOMPLETE_TYPES, true)) {
            throw new BadRequestHttpException(sprintf('Invalid parameter "type" "%s" given.', $type));
        }

        $value = $request->query->get('value');
        switch ($type) {
            case self::AUTOCOMPLETE_TYPE_CITY:
                return new JsonResponse(['cities' => array_values(array_unique(array_merge(
                    $committeeRepository->findCitiesForReferentAutocomplete($referent, $value),
                    $eventRepository->findCitiesForReferentAutocomplete($referent, $value)),
                    \SORT_REGULAR
                ))]);
            case self::AUTOCOMPLETE_TYPE_COMMITTEE:
                return new JsonResponse(['committees' => $committeeRepository->findApprovedForReferentAutocomplete($referent, $value)]);
            case self::AUTOCOMPLETE_TYPE_COUNTRY:
                return new JsonResponse(['countries' => $referent->getManagedArea()->getOnlyManagedCountryCodes($value)]);
        }

        return new JsonResponse('NOK', Response::HTTP_BAD_REQUEST);
    }
}
