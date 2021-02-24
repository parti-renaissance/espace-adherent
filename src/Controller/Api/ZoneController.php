<?php

namespace App\Controller\Api;

use App\Controller\EnMarche\AccessDelegatorTrait;
use App\Entity\Geo\Zone;
use App\Geo\ManagedZoneProvider;
use App\Repository\Geo\ZoneRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class ZoneController extends AbstractController
{
    use AccessDelegatorTrait;

    private const SUGGESTIONS_PER_TYPE = 5;
    private const ACTIVE_ONLY = true;

    private const TYPES = [
        Zone::CUSTOM,
        Zone::FOREIGN_DISTRICT,
        Zone::COUNTRY,
        Zone::CONSULAR_DISTRICT,
        Zone::REGION,
        Zone::DEPARTMENT,
        Zone::CITY,
        Zone::DISTRICT,
        Zone::CITY_COMMUNITY,
        Zone::CANTON,
        Zone::BOROUGH,
    ];

    /**
     * @Route("/zone/autocompletion", name="api_zone_autocomplete", condition="request.isXmlHttpRequest()", methods={"GET"})
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function autocomplete(
        Request $request,
        ManagedZoneProvider $managedZoneProvider,
        ZoneRepository $repository,
        TranslatorInterface $translator
    ): Response {
        $term = (string) $request->query->get('q', '');
        $spaceType = (string) $request->query->get('space_type', '');

        $max = $request->query->getInt('page_limit', self::SUGGESTIONS_PER_TYPE);
        $max = min($max, self::SUGGESTIONS_PER_TYPE);

        $activeOnly = $request->query->getBoolean('active_only', self::ACTIVE_ONLY);

        $user = $this->getMainUser($request->getSession());
        $managedZones = $managedZoneProvider->getManagedZones($user, $spaceType);

        $zones = $repository->searchByTermAndManagedZonesGroupedByType(
            $term,
            $managedZones,
            self::TYPES,
            $activeOnly,
            $max
        );

        $results = $this->normalizeZoneForSelect2($translator, $zones);

        return new JsonResponse([
            'results' => array_values($results),
            'pagination' => [
                'more' => false,
            ],
        ]);
    }

    /**
     * @param Zone[] $zones
     */
    private function normalizeZoneForSelect2(TranslatorInterface $translator, array $zones): array
    {
        $results = array_fill_keys(self::TYPES, null);

        foreach ($zones as $zone) {
            $type = $zone->getType();
            $groupText = $translator->trans("geo_zone.{$type}") ?: $type;

            if (!isset($results[$type])) {
                $results[$type] = [
                    'text' => $groupText,
                    'children' => [],
                ];
            }

            $results[$type]['children'][] = [
                'id' => $zone->getId(),
                'text' => $zone->getNameCode(),
            ];
        }

        return array_values(array_filter($results));
    }
}
