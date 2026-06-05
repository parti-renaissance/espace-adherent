<?php

declare(strict_types=1);

namespace App\Controller\Api\Vox;

use App\AdherentMessage\PublicationZone;
use App\Entity\Adherent;
use App\Entity\AdherentMandate\ElectedRepresentativeAdherentMandate;
use App\Entity\Geo\Zone;
use App\JeMengage\Timeline\DataProvider;
use App\JeMengage\Timeline\Indexer\IndexerTimelineProvider;
use App\JeMengage\Timeline\Indexer\TimelineSessionResolver;
use App\JeMengage\Timeline\TimelineFeedTypeEnum;
use App\JeMengage\Timeline\UserScopeTargetResolver;
use App\Repository\Geo\ZoneRepository;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Uuid;

#[IsGranted('ROLE_OAUTH_SCOPE_JEMARCHE_APP')]
#[Route(path: '/v3/je-mengage/timeline_feeds', name: 'api_get_jemarche_timeline_feeds', methods: ['GET'])]
class GetTimelineFeedsController extends AbstractController
{
    /**
     * Feed types exposed in the JeMengage mobile timeline, passed to Algolia as tag filters.
     */
    private const array TIMELINE_FEED_TYPES = [
        TimelineFeedTypeEnum::NEWS,
        TimelineFeedTypeEnum::EVENT,
        TimelineFeedTypeEnum::ACTION,
        TimelineFeedTypeEnum::PUBLICATION,
        TimelineFeedTypeEnum::TRANSACTIONAL_MESSAGE,
        TimelineFeedTypeEnum::SOCIAL_NETWORK_POST,
    ];

    public function __construct(
        private readonly ZoneRepository $zoneRepository,
        private readonly UserScopeTargetResolver $scopeTargetResolver,
        private readonly IndexerTimelineProvider $indexerTimelineProvider,
        private readonly TimelineSessionResolver $sessionResolver,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(#[CurrentUser] Adherent $user, Request $request, DataProvider $dataProvider): JsonResponse
    {
        if (($page = $request->query->getInt('page')) < 0) {
            $page = 0;
        }

        $appSessionId = trim((string) $request->query->get('session_id'));
        $sessionId = $this->sessionResolver->resolve($user, '' === $appSessionId ? Uuid::v4()->toRfc4122() : $appSessionId);

        try {
            return $this->json($this->indexerTimelineProvider->findItems($user, $page, $sessionId));
        } catch (\RuntimeException $exception) {
            $this->logger->warning('Timeline ranker failed, falling back to Algolia.', [
                'exception' => $exception,
                'user_id' => $user->getId(),
            ]);
            // No return/throw: execution falls through to the Algolia path below.
        }

        $userId = $user->getId();
        $tags = $user->tags ?? [];

        $baseOr = [
            'is_national:true',
            "adherent_ids:$userId",
            'type:publication',
        ];

        // Assembly zone + the user's own city, so militant events — indexed with their city code
        // only — surface in the right local timelines.
        if ($assemblyZone = $user->getAssemblyZone()) {
            $baseOr[] = 'zone_codes:'.$assemblyZone->getTypeCode();
        }

        foreach ($user->getZonesOfType(Zone::CITY) as $zone) {
            $baseOr[] = 'zone_codes:'.$zone->getTypeCode();
        }

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
        $userZonesByType = $this->zonesForUserGroupedByType($user);

        $zoneClauses = [];
        foreach (PublicationZone::ZONE_TYPES as $type) {
            $codes = $userZonesByType[$type] ?? [];

            $zoneCond = ['NOT type:publication', 'audience.zone:false', 'audience.include:"zone:'.$type.':none"'];

            foreach ($codes as $code) {
                $zoneCond[] = 'audience.include:"zone:'.$type.':'.$code.'"';
            }

            $zoneClauses[] = '('.implode(' OR ', $zoneCond).')';
        }

        if ($membership = $user->getCommitteeMembership()) {
            $committeeUuid = $membership->getCommittee()->getUuid()->toRfc4122();
            $committeeClause = '(NOT type:publication OR audience.committee:false OR audience.include:"committee:'.$committeeUuid.'")';
        } else {
            $committeeClause = '(NOT type:publication OR audience.committee:false)';
        }

        $ageCivilityClauses = [
            '(audience.age_min = 0 OR audience.age_min <= '.($user->getAge() ?? 0).')',
            '(audience.age_max = 0 OR audience.age_max >= '.($user->getAge() ?? 0).')',
            '(NOT type:publication OR audience.civility:false OR audience.include:"gender:'.$user->getGender().'")',
            '(NOT type:publication OR audience.committee_member:2 OR audience.committee_member:'.(null !== $user->getCommitteeMembership() ? 1 : 0).')',
        ];

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
                $dateClauses[] = \sprintf('(audience.%1$ssince = 0 OR audience.%1$ssince <= '.($timestamp = $date->getTimestamp()).')', $key);
                $dateClauses[] = \sprintf('(audience.%1$sbefore = 0 OR audience.%1$sbefore >= '.$timestamp.')', $key);
            } else {
                $dateClauses[] = \sprintf('audience.%ssince = 0', $key);
                $dateClauses[] = \sprintf('audience.%sbefore = 0', $key);
            }
        }

        $userFilter = [];
        if ($zone = $request->query->get('zone')) {
            $zoneFilter = 'is_national:true';
            if (Uuid::isValid($zone) && $zone = $this->zoneRepository->findOneByUuid($zone)) {
                $zoneFilter = 'zone_codes:'.$zone->getTypeCode();
            }
            $userFilter[] = $zoneFilter;
        }

        if (($committee = $request->query->get('committee')) && Uuid::isValid($committee)) {
            $userFilter[] = 'committee_uuid:'.$committee;
        }

        if ($instance = $request->query->get('instance')) {
            $filterValue = match ($instance) {
                'committee' => $user->getCommitteeMembership()?->getCommitteeUuid(),
                'circonscription' => ($user->isForeignResident()
                    ? $user->getZonesOfType(Zone::FOREIGN_DISTRICT)
                    : $user->getZonesOfType(Zone::DISTRICT))[0]?->getTypeCode(),
                'assembly' => $user->getAssemblyZone()?->getTypeCode(),
                'agora' => ($user->agoraMemberships->first() ?: null)?->agora->getUuid()->toRfc4122(),
                default => null,
            };

            $filterKey = match ($instance) {
                'committee' => 'committee_uuid',
                'circonscription', 'assembly' => 'zone_codes',
                'agora' => 'agora_uuid',
                default => null,
            };

            if ($filterKey && $filterValue) {
                $userFilter[] = \sprintf('%s:%s', $filterKey, $filterValue);
            }
        }

        // Construction finale
        $parts = array_filter([
            ...$userFilter,
            $baseClause,
            $tagClause,
            ...array_unique($excludeTagConditions),
            ...$zoneClauses,
            ...$ageCivilityClauses,
            $committeeClause,
            $mandateClause,
            ...$excludeMandatClauses,
            $declaredMandateClause,
            ...$excludeDeclaredMandateClauses,
            ...$dateClauses,
            $this->buildScopeTargetClause($user),
        ]);

        $tagFilters = [self::TIMELINE_FEED_TYPES];

        return $this->json($dataProvider->findItems($user, $page, $parts, $tagFilters));
    }

    private function zonesForUserGroupedByType(Adherent $user): array
    {
        $byType = array_fill_keys(PublicationZone::ZONE_TYPES, []);

        foreach ($user->getDeepZones() as $zone) {
            $type = $zone->getType();
            $code = $zone->getCode();
            if (isset($byType[$type]) && $code) {
                $byType[$type][] = $code;
            }
        }

        foreach ($byType as $t => $list) {
            $byType[$t] = array_values(array_unique(array_filter($list)));
        }

        return $byType;
    }

    /**
     * Builds the Algolia filter clause for scope_targets.
     * Publications without scopeTargets are visible to all.
     * Publications with scopeTargets are visible only to users having one of the targeted roles
     * or being team members with matching scope and role.
     */
    private function buildScopeTargetClause(Adherent $user): string
    {
        $clause = '(NOT type:publication OR audience.scope_targets:false';

        foreach ($this->scopeTargetResolver->resolve($user) as $key) {
            $clause .= ' OR audience.include:"scope_targets:'.$key.'"';
        }

        return $clause.')';
    }
}
