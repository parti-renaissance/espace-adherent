<?php

declare(strict_types=1);

namespace App\Controller\Api\Zone;

use App\AdherentSpace\AdherentSpaceEnum;
use App\Entity\Geo\Zone;
use App\Geo\ManagedZoneProvider;
use App\Repository\Geo\ZoneRepository;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/zone/autocompletion', name: 'api_zone_autocomplete', methods: ['GET'])]
#[Route(path: '/v3/zone/autocompletion', name: 'api_v3_zone_autocomplete', methods: ['GET'])]
class ZoneAutocompleteSelect2Controller extends AbstractZoneAutocompleteController
{
    private const SUGGESTIONS_PER_TYPE = 5;

    public function __invoke(
        Request $request,
        ManagedZoneProvider $managedZoneProvider,
        ZoneRepository $repository,
        TranslatorInterface $translator,
    ): Response {
        if (empty($spaceType = (string) $request->query->get('space_type'))) {
            throw new BadRequestException('Space type missing');
        }

        if (($max = $request->query->getInt('page_limit', self::SUGGESTIONS_PER_TYPE)) > 50) {
            $max = self::SUGGESTIONS_PER_TYPE;
        }

        $managedZones = [];

        if ($this->getUser()) {
            $user = $this->getMainUser($request->getSession());
            $managedZones = $managedZoneProvider->getManagedZones($user, $spaceType);
        }

        $filter = $this->getFilter($request);

        if (AdherentSpaceEnum::CANDIDATE_JECOUTE === $spaceType) {
            $filter->setTypes(Zone::CANDIDATE_TYPES);
        }

        $zones = $repository->searchByFilterInsideManagedZones($filter, $managedZones, $max);

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
        $results = array_fill_keys(Zone::TYPES, null);

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
                'text' => $zone->getPostalCodeAsString() ? \sprintf('%s (%s)', $zone->getName(), $zone->getPostalCodeAsString()) : $zone->getNameCode(),
            ];
        }

        return array_values(array_filter($results));
    }
}
