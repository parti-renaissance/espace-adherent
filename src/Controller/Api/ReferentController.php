<?php

namespace AppBundle\Controller\Api;

use AppBundle\Entity\Adherent;
use AppBundle\Repository\CommitteeRepository;
use AppBundle\Repository\EventRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/statistics")
 * @Security("is_granted('ROLE_OAUTH_SCOPE_READ:STATS')")
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
     *
     * @Entity("referent", expr="repository.findReferent(referent)", converter="querystring")
     */
    public function searchAutocompleteAction(
        Adherent $referent,
        Request $request,
        CommitteeRepository $committeeRepository,
        EventRepository $eventRepository
    ): Response {
        // parameter `type` should have a value different from an empty string, but parameter `value` can be an empty string
        if (!($type = $request->query->get('type')) || !$request->query->has('value')) {
            throw new BadRequestHttpException('The parameters "type" and "value" are required.');
        }

        if (!\in_array($type, self::AUTOCOMPLETE_TYPES, true)) {
            throw new BadRequestHttpException(sprintf('Invalid parameter "type" "%" given.', $type));
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
    }
}
