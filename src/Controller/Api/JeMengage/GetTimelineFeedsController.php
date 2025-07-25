<?php

namespace App\Controller\Api\JeMengage;

use App\Entity\Adherent;
use App\Entity\AdherentMandate\ElectedRepresentativeAdherentMandate;
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

        $userId = $user->getId();
        $zoneCode = $user->getAssemblyZone()?->getTypeCode();
        $tags = $user->tags ?? [];

        $baseOr = [
            'is_national:true',
            "adherent_ids:$userId",
            $zoneCode ? "zone_codes:$zoneCode" : null,
            'type:publication',
        ];

        $baseOr = array_filter($baseOr);
        $baseClause = '('.implode(' OR ', $baseOr).')';

        $includeTagConditions = [];
        $excludeTagConditions = [];

        foreach ($tags as $tag) {
            $parts = explode(':', $tag);
            $prefix = '';
            foreach ($parts as $i => $part) {
                $prefix = (0 === $i) ? $part : "$prefix:$part";
                $includeTagConditions[] = 'audience.include:"tag:'.$prefix.'"';
                $excludeTagConditions[] = 'NOT audience.exclude:"tag:'.$prefix.'"';
            }
        }

        $tagClause = '(NOT type:publication OR audience.tag:false';
        if ($includeTagConditions) {
            $tagClause .= ' OR '.implode(' OR ', array_unique($includeTagConditions));
        }
        $tagClause .= ')';

        // Bloc 3 : zone
        $zoneClause = $zoneCode ? '(NOT type:publication OR audience.zone:false OR audience.include:"zone:'.$zoneCode.'")' : null;

        if ($membership = $user->getCommitteeMembership()) {
            $committeeUuid = $membership->getCommittee()->getUuid()->toString();
            $committeeClause = '(NOT type:publication OR audience.committee:false OR audience.include:"committee:'.$committeeUuid.'")';
        } else {
            $committeeClause = '(NOT type:publication OR audience.committee:false)';
        }

        $mandateTypes = array_map(fn (ElectedRepresentativeAdherentMandate $m) => $m->mandateType, $user->findElectedRepresentativeMandates(true));
        $excludeMandatClauses = [];
        $mandateClause = '(NOT type:publication OR audience.mandate_type:false)';

        if (!empty($mandateTypes)) {
            $mandateInclude = array_map(static fn (string $type) => 'audience.include:"mandate_type:'.$type.'"', $mandateTypes);
            $excludeMandatClauses = array_map(static fn (string $type) => 'NOT audience.exclude:"mandate_type:'.$type.'"', $mandateTypes);
            $mandateClause = '(NOT type:publication OR audience.mandate_type:false OR '.implode(' OR ', $mandateInclude).')';
        }

        $declaredMandates = $user->getMandates();
        $excludeDeclaredMandateClauses = [];
        $declaredMandateClause = '(NOT type:publication OR audience.declared_mandate:false)';

        if (!empty($declaredMandates)) {
            $declaredMandateInclude = array_map(fn (string $type) => 'audience.include:"declared_mandate:'.$type.'"', $declaredMandates);
            $excludeDeclaredMandateClauses = array_map(fn (string $type) => 'NOT audience.exclude:"declared_mandate:'.$type.'"', $declaredMandates);
            $declaredMandateClause = '(NOT type:publication OR audience.declared_mandate:false OR '.implode(' OR ', $declaredMandateInclude).')';
        }

        $dateClauses = [];

        foreach ([
            'first_membership_' => $user->getFirstMembershipDonation(),
            'last_membership_' => $user->getLastMembershipDonation(),
            'registered_' => $user->getRegisteredAt(),
        ] as $key => $date) {
            if ($date) {
                $dateClauses[] = \sprintf('(audience.%1$ssince = 0 OR audience.include.%1$ssince <= '.($timestamp = $date->getTimestamp()).')', $key);
                $dateClauses[] = \sprintf('(audience.%1$sbefore = 0 OR audience.include.%1$sbefore >= '.$timestamp.')', $key);
            } else {
                $dateClauses[] = 'audience.first_membership_since = 0';
                $dateClauses[] = 'audience.first_membership_before = 0';
            }
        }

        // Construction finale
        $parts = array_filter([
            $baseClause,
            $tagClause,
            ...array_unique($excludeTagConditions),
            $zoneClause,
            $committeeClause,
            $mandateClause,
            ...$excludeMandatClauses,
            $declaredMandateClause,
            ...$excludeDeclaredMandateClauses,
            ...$dateClauses,
        ]);

        $tagFilters = [[
            TimelineFeedTypeEnum::NEWS,
            TimelineFeedTypeEnum::EVENT,
            TimelineFeedTypeEnum::ACTION,
            TimelineFeedTypeEnum::PUBLICATION,
        ]];

        return $this->json($dataProvider->findItems($user, $page, $parts, $tagFilters));
    }
}
