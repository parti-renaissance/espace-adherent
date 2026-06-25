<?php

declare(strict_types=1);

namespace App\Controller\Api\Vox;

use App\Entity\Adherent;
use App\JeMengage\Timeline\CandidateSelection\AudienceContext;
use App\JeMengage\Timeline\CandidateSelection\AudienceContextFactory;
use App\JeMengage\Timeline\CandidateSelection\RequestFilterCondition;
use App\JeMengage\Timeline\CandidateSelection\TimelineRequestFilter;
use App\JeMengage\Timeline\CandidateSelection\TimelineRequestFilterFactory;
use App\JeMengage\Timeline\DataProvider;
use App\JeMengage\Timeline\Indexer\IndexerTimelineProvider;
use App\JeMengage\Timeline\Indexer\TimelineSessionResolver;
use App\JeMengage\Timeline\TimelineFeedTypeEnum;
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
        private readonly AudienceContextFactory $contextFactory,
        private readonly TimelineRequestFilterFactory $requestFilterFactory,
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

        $filter = $this->requestFilterFactory->createFromRequest($request, $user);

        try {
            return $this->json($this->indexerTimelineProvider->findItems($user, $page, $sessionId, $filter));
        } catch (\RuntimeException $exception) {
            $this->logger->error('Timeline ranker failed, falling back to Algolia.', [
                'exception' => $exception,
                'user_id' => $user->getId(),
            ]);
        }

        // Algolia fallback. The user targeting dimensions come from the same AudienceContext the
        // ranker path uses (single source); the clause strings below are characterization-locked
        // (GetTimelineFeedsAlgoliaClausesTest golden files).
        $context = $this->contextFactory->create($user);

        $baseOr = [
            'is_national:true',
            'adherent_ids:'.$context->profile->userId,
            'type:publication',
        ];

        // Assembly zone + the user's own city, so militant events — indexed with their city code
        // only — surface in the right local timelines.
        foreach ($context->reachZones as $reachZone) {
            $baseOr[] = 'zone_codes:'.$this->algoliaReachCode($reachZone);
        }

        $baseOr = array_filter($baseOr);
        $baseClause = '('.implode(' OR ', $baseOr).')';

        $includeTagConditions = [];
        $excludeTagConditions = [];

        foreach ($context->tagPrefixes as $prefix) {
            $includeTagConditions[] = 'audience.include:"tag:'.$prefix.'"';
            $excludeTagConditions[] = 'NOT audience.exclude:"tag:'.$prefix.'"';
        }

        $tagClause = '(NOT type:publication OR audience.tag:false';
        if ($includeTagConditions) {
            $tagClause .= ' OR '.implode(' OR ', array_unique($includeTagConditions));
        }
        $tagClause .= ')';

        $zoneClauses = [];
        foreach ($context->zoneCodesByType as $type => $codes) {
            $zoneCond = ['NOT type:publication', 'audience.zone:false', 'audience.include:"zone:'.$type.':none"'];

            foreach ($codes as $code) {
                $zoneCond[] = 'audience.include:"zone:'.$type.':'.$code.'"';
            }

            $zoneClauses[] = '('.implode(' OR ', $zoneCond).')';
        }

        if ([] !== $context->profile->committees) {
            $committeeClause = '(NOT type:publication OR audience.committee:false OR audience.include:"committee:'.$context->profile->committees[0].'")';
        } else {
            $committeeClause = '(NOT type:publication OR audience.committee:false)';
        }

        $age = $context->profile->age ?? 0;
        $ageCivilityClauses = [
            '(audience.age_min = 0 OR audience.age_min <= '.$age.')',
            '(audience.age_max = 0 OR audience.age_max >= '.$age.')',
            '(NOT type:publication OR audience.civility:false OR audience.include:"gender:'.$context->profile->civility.'")',
            '(NOT type:publication OR audience.committee_member:2 OR audience.committee_member:'.$context->profile->committeeMember.')',
        ];

        $mandateTypes = $context->profile->mandateTypes;
        $excludeMandatClauses = [];
        $mandateClause = '(NOT type:publication OR audience.mandate_type:false)';

        if (!empty($mandateTypes)) {
            $mandateInclude = array_map(static fn (string $type) => 'audience.include:"mandate_type:'.$type.'"', $mandateTypes);
            $excludeMandatClauses = array_map(static fn (string $type) => 'NOT audience.exclude:"mandate_type:'.$type.'"', $mandateTypes);
            $mandateClause = '(NOT type:publication OR audience.mandate_type:false OR '.implode(' OR ', $mandateInclude).')';
        }

        $declaredMandates = $context->profile->declaredMandates;
        $excludeDeclaredMandateClauses = [];
        $declaredMandateClause = '(NOT type:publication OR audience.declared_mandate:false)';

        if (!empty($declaredMandates)) {
            $declaredMandateInclude = array_map(fn (string $type) => 'audience.include:"declared_mandate:'.$type.'"', $declaredMandates);
            $excludeDeclaredMandateClauses = array_map(fn (string $type) => 'NOT audience.exclude:"declared_mandate:'.$type.'"', $declaredMandates);
            $declaredMandateClause = '(NOT type:publication OR audience.declared_mandate:false OR '.implode(' OR ', $declaredMandateInclude).')';
        }

        $dateClauses = [];

        foreach ([
            'first_membership_' => $context->profile->firstMembershipDate,
            'last_membership_' => $context->profile->lastMembershipDate,
            'registered_' => $context->profile->registeredDate,
        ] as $key => $date) {
            if ($date) {
                $timestamp = new \DateTimeImmutable($date)->getTimestamp();
                $dateClauses[] = \sprintf('(audience.%1$ssince = 0 OR audience.%1$ssince <= '.$timestamp.')', $key);
                $dateClauses[] = \sprintf('(audience.%1$sbefore = 0 OR audience.%1$sbefore >= '.$timestamp.')', $key);
            } else {
                $dateClauses[] = \sprintf('audience.%ssince = 0', $key);
                $dateClauses[] = \sprintf('audience.%sbefore = 0', $key);
            }
        }

        $userFilter = $this->buildUserFilterClauses($filter);

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
            $this->buildScopeTargetClause($context),
        ]);

        $tagFilters = [self::TIMELINE_FEED_TYPES];

        return $this->json($dataProvider->findItems($user, $page, $parts, $tagFilters));
    }

    /**
     * @return string[]
     */
    private function buildUserFilterClauses(?TimelineRequestFilter $filter): array
    {
        if (null === $filter) {
            return [];
        }

        $clauses = [];
        foreach ($filter->conditions as $condition) {
            $clauses[] = match ($condition->kind) {
                RequestFilterCondition::NATIONAL => 'is_national:true',
                RequestFilterCondition::ZONE => 'zone_codes:'.$this->algoliaReachCode((string) $condition->value),
                RequestFilterCondition::COMMITTEE => 'committee_uuid:'.$condition->value,
                RequestFilterCondition::AGORA => 'agora_uuid:'.$condition->value,
                default => null,
            };
        }

        return array_values(array_filter($clauses));
    }

    /**
     * Canonical "type:code" -> Algolia zone_codes shape "type_code" (Zone::getTypeCode()). Zone
     * codes never contain a colon (the canonical shape splits on the first one), so replacing the
     * first occurrence is exact.
     */
    private function algoliaReachCode(string $canonicalZone): string
    {
        return preg_replace('/:/', '_', $canonicalZone, 1);
    }

    /**
     * Builds the Algolia filter clause for scope_targets.
     * Publications without scopeTargets are visible to all.
     * Publications with scopeTargets are visible only to users having one of the targeted roles
     * or being team members with matching scope and role.
     */
    private function buildScopeTargetClause(AudienceContext $context): string
    {
        $clause = '(NOT type:publication OR audience.scope_targets:false';

        foreach ($context->profile->scopeTargets as $key) {
            $clause .= ' OR audience.include:"scope_targets:'.$key.'"';
        }

        return $clause.')';
    }
}
