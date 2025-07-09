<?php

namespace App\Controller\Api\JeMengage;

use App\Entity\Adherent;
use App\JeMengage\Timeline\DataProvider;
use App\JeMengage\Timeline\TimelineFeedTypeEnum;
use App\OAuth\Model\DeviceApiUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_OAUTH_SCOPE_JEMARCHE_APP')]
#[Route(path: '/v3/je-mengage/timeline_feeds', name: 'api_get_jemarche_timeline_feeds', methods: ['GET'])]
class GetTimelineFeedsController extends AbstractController
{
    /**
     * @param Adherent|DeviceApiUser $user
     */
    public function __invoke(UserInterface $user, Request $request, DataProvider $dataProvider): JsonResponse
    {
        if (($page = $request->query->getInt('page')) < 0) {
            $page = 0;
        }

        $baseFilters = [
            'is_national:true',
            'adherent_ids:'.$user->getId(),
        ];

        if ($assemblyZone = $user->getAssemblyZone()) {
            $baseFilters[] = 'zone_codes:'.$assemblyZone->getTypeCode();
        }

        $showFilters = [];
        $hideFilters = [];

        foreach ($user->tags as $tag) {
            $parts = explode(':', $tag);
            $prefix = '';
            foreach ($parts as $i => $part) {
                $prefix = 0 === $i ? $part : "$prefix:$part";

                $showFilters[] = 'audience.show_tags:"'.$prefix.'"';
                $hideFilters[] = 'NOT audience.hide_tags:"'.$prefix.'"';
            }
        }

        $accessConditions = array_merge($baseFilters, $showFilters);
        $filters = [];
        $filters[] = '('.implode(' OR ', array_unique($accessConditions)).')';

        if ($hideFilters) {
            $filters[] = implode(' AND ', array_unique($hideFilters));
        }

        $tagFilters = [[
            TimelineFeedTypeEnum::NEWS,
            TimelineFeedTypeEnum::EVENT,
            TimelineFeedTypeEnum::ACTION,
            TimelineFeedTypeEnum::PUBLICATION,
        ]];

        return $this->json($dataProvider->findItems($user, $page, $filters, $tagFilters));
    }
}
