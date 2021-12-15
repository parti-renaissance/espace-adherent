<?php

namespace App\Controller\Api\JeMengage;

use App\Algolia\SearchService;
use App\Entity\Algolia\AlgoliaJeMengageTimelineFeed;
use App\JeMengage\Timeline\TimelineFeedTypeEnum;
use App\OAuth\Model\DeviceApiUser;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @Route(
 *     "/v3/je-mengage/timeline_feeds",
 *     name="api_get_jemarche_timeline_feeds",
 *     methods={"GET"}
 * )
 *
 * @Security("is_granted('ROLE_OAUTH_SCOPE_JEMARCHE_APP')")
 */
class GetTimelineFeedsController extends AbstractController
{
    public function __invoke(UserInterface $user, Request $request, SearchService $searchService): JsonResponse
    {
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

        $filters = ['is_national:true', 'zone_codes:department_'.$dpt];
        $tagFilters = [];

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
