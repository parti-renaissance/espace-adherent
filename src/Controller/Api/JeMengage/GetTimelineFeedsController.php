<?php

namespace App\Controller\Api\JeMengage;

use App\Algolia\SearchService;
use App\Entity\Adherent;
use App\Entity\Algolia\AlgoliaJeMengageTimelineFeed;
use App\Intl\FranceCitiesBundle;
use App\JeMengage\Timeline\TimelineFeedTypeEnum;
use App\OAuth\Model\DeviceApiUser;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/v3/je-mengage/timeline_feeds', name: 'api_get_jemarche_timeline_feeds', methods: ['GET'])]
#[IsGranted('ROLE_OAUTH_SCOPE_JEMARCHE_APP')]
class GetTimelineFeedsController extends AbstractController
{
    public function __invoke(Request $request, SearchService $searchService): JsonResponse
    {
        /** @var Adherent|DeviceApiUser $user */
        $user = $this->getUser();
        if (($page = $request->query->getInt('page')) < 0) {
            $page = 0;
        }

        if (!$postalCode = $request->query->get('postal_code')) {
            return $this->json('Invalid postal code provide', Response::HTTP_BAD_REQUEST);
        }

        $postalCode = str_pad($postalCode, 5, '0', \STR_PAD_LEFT);
        $dpt = substr($postalCode, 0, 2);
        if (\in_array($dpt, [97, 98])) {
            $dpt = substr($postalCode, 0, 3);
        }

        if (75 === (int) $dpt && $code = array_search($postalCode, FranceCitiesBundle::SPECIAL_CITY_DISTRICTS[FranceCitiesBundle::CUSTOM_CITY_CODE_PARIS])) {
            $zoneFilter = 'zone_codes:borough_'.$code;
        } else {
            $zoneFilter = 'zone_codes:department_'.$dpt;
        }

        $filters = ['is_national:true', $zoneFilter];

        $tagFilters = [[
            TimelineFeedTypeEnum::NEWS,
            TimelineFeedTypeEnum::EVENT,
        ]];

        if ($user instanceof DeviceApiUser) {
            $tagFilters[] = [
                TimelineFeedTypeEnum::NEWS,
                TimelineFeedTypeEnum::SURVEY,
            ];
        } else {
            $filters[] = 'adherent_ids:'.$user->getId();
        }

        $timelineFeeds = $searchService->rawSearch(AlgoliaJeMengageTimelineFeed::class, '', [
            'page' => $page,
            'attributesToHighlight' => [],
            'filters' => implode(' OR ', $filters),
            'tagFilters' => $tagFilters,
        ]);

        return $this->json($timelineFeeds, Response::HTTP_OK);
    }
}
