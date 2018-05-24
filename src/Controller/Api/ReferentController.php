<?php

namespace AppBundle\Controller\Api;

use AppBundle\Repository\CommitteeRepository;
use AppBundle\Repository\EventRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @Route("/referent")
 * @Security("is_granted('ROLE_REFERENT')")
 */
class ReferentController extends Controller
{
    private const AUTOCOMPLETE_TYPE_CITY = 'city';
    private const AUTOCOMPLETE_TYPE_COMMITTEE = 'committee';
    private const AUTOCOMPLETE_TYPE_COUNTRY = 'country';

    private const AUTOCOMPLETE_TYPES = [
        self::AUTOCOMPLETE_TYPE_CITY,
        self::AUTOCOMPLETE_TYPE_COMMITTEE,
        self::AUTOCOMPLETE_TYPE_COUNTRY,
    ];

    /**
     * @Route("/search/autocomplete", name="api_referent_space_search_autocomplete")
     * @Method("GET")
     */
    public function searchAutocompleteAction(Request $request, CommitteeRepository $committeeRepository, EventRepository $eventRepository): Response
    {
        if (!($type = $request->query->get('type')) || !($value = $request->query->get('value'))) {
            throw new BadRequestHttpException('The parameters "type" and "value" are required.');
        }

        if (!in_array($type, self::AUTOCOMPLETE_TYPES, true)) {
            throw new BadRequestHttpException(sprintf('Invalid parameter "type" "%" given.', $type));
        }
        $referent = $this->getUser();

        switch ($type) {
            case self::AUTOCOMPLETE_TYPE_CITY:
                return new JsonResponse(['cities' => array_unique(array_merge(
                    $committeeRepository->findCitiesForReferentAutocomplete($referent, $value),
                    $eventRepository->findCitiesForReferentAutocomplete($referent, $value)),
                    SORT_REGULAR
                )]);
            case self::AUTOCOMPLETE_TYPE_COMMITTEE:
                return new JsonResponse(['committees' => $committeeRepository->findApprovedForReferentAutocomplete($referent, $value)]);
            case self::AUTOCOMPLETE_TYPE_COUNTRY:
                return new JsonResponse(['countries' => $referent->getManagedArea()->getOnlyManagedCountryCodes($value)]);
        }
    }
}
