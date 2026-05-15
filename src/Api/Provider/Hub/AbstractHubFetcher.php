<?php

declare(strict_types=1);

namespace App\Api\Provider\Hub;

use ApiPlatform\Metadata\Operation;
use App\Entity\Action\Action;
use App\Entity\Event\Event;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;

abstract class AbstractHubFetcher
{
    abstract protected function buildQuery(array $filters, array $apiContext, ?Operation $operation): QueryBuilder;

    abstract protected function entityType(): string;

    abstract protected function extractBeginAt(Event|Action $entity): ?\DateTimeInterface;

    abstract protected function extractFinishAt(Event|Action $entity): ?\DateTimeInterface;

    abstract protected function extractParticipantsCount(Event|Action $entity): int;

    /**
     * @return HubItemRow[]
     */
    public function fetch(array $filters, array $apiContext, ?Operation $operation, int $limit): array
    {
        $queryBuilder = $this->buildQuery($filters, $apiContext, $operation);
        $queryBuilder->setMaxResults($limit);

        // Use Doctrine's Paginator with fetchJoinCollection: it bounds DISTINCT entities,
        // not raw SQL rows multiplied by left-joined collections (participants, zones, …).
        $paginator = new Paginator($queryBuilder->getQuery(), true);
        $paginator->setUseOutputWalkers(false);

        /** @var array<Event|Action> $entities */
        $entities = iterator_to_array($paginator);

        $userCoords = $this->extractUserCoords($filters);
        $now = new \DateTimeImmutable();

        return array_map(fn (Event|Action $entity) => $this->toRow($entity, $userCoords, $now), $entities);
    }

    public function count(array $filters, array $apiContext, ?Operation $operation): int
    {
        $queryBuilder = $this->buildQuery($filters, $apiContext, $operation);
        $alias = $queryBuilder->getRootAliases()[0];

        $queryBuilder
            ->select(\sprintf('COUNT(DISTINCT %s.id)', $alias))
            ->resetDQLParts(['orderBy'])
        ;

        $dql = $queryBuilder->getDQL();
        $queryBuilder->setParameters($queryBuilder->getParameters()->filter(
            static fn ($parameter) => str_contains($dql, ':'.$parameter->getName())
        ));

        return (int) $queryBuilder->getQuery()->getSingleScalarResult();
    }

    protected function applyHubDateFilter(QueryBuilder $queryBuilder, array $filters, string $dateField, string $filterKey = 'beginAt'): void
    {
        $value = $filters[$filterKey] ?? null;

        if (\is_string($value) && '' !== $value) {
            $paramName = 'hub_'.$filterKey.'_start';
            $queryBuilder
                ->andWhere(\sprintf('%s LIKE :%s', $dateField, $paramName))
                ->setParameter($paramName, $value.'%')
            ;

            return;
        }

        if (!\is_array($value)) {
            return;
        }

        $prefix = 'hub_'.$filterKey.'_';

        if (isset($value['strictly_after'])) {
            $queryBuilder
                ->andWhere(\sprintf('%s > :%sstrictly_after', $dateField, $prefix))
                ->setParameter($prefix.'strictly_after', new \DateTimeImmutable($value['strictly_after']))
            ;
        }

        if (isset($value['after'])) {
            $queryBuilder
                ->andWhere(\sprintf('%s >= :%safter', $dateField, $prefix))
                ->setParameter($prefix.'after', new \DateTimeImmutable($value['after']))
            ;
        }

        if (isset($value['strictly_before'])) {
            $queryBuilder
                ->andWhere(\sprintf('%s < :%sstrictly_before', $dateField, $prefix))
                ->setParameter($prefix.'strictly_before', new \DateTimeImmutable($value['strictly_before']))
            ;
        }

        if (isset($value['before'])) {
            $queryBuilder
                ->andWhere(\sprintf('%s <= :%sbefore', $dateField, $prefix))
                ->setParameter($prefix.'before', new \DateTimeImmutable($value['before']))
            ;
        }
    }

    /**
     * @return array{float, float}|null
     */
    protected function extractUserCoords(array $filters): ?array
    {
        if (!isset($filters['lat'], $filters['lng']) || !is_numeric($filters['lat']) || !is_numeric($filters['lng'])) {
            return null;
        }

        return [(float) $filters['lat'], (float) $filters['lng']];
    }

    private function haversineKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        return 6371 * acos(
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($lng2) - deg2rad($lng1))
            + sin(deg2rad($lat1)) * sin(deg2rad($lat2))
        );
    }

    /**
     * @param array{float, float}|null $userCoords
     */
    private function toRow(Event|Action $entity, ?array $userCoords, \DateTimeImmutable $now): HubItemRow
    {
        $beginAt = $this->extractBeginAt($entity) ?? $now;
        $priority = $beginAt >= $now ? 1 : 0;
        $timeToBegin = abs($now->getTimestamp() - $beginAt->getTimestamp());

        $distance = null;
        if (null !== $userCoords) {
            $latitude = $entity->getLatitude();
            $longitude = $entity->getLongitude();

            if (null !== $latitude && null !== $longitude) {
                $distance = $this->haversineKm($userCoords[0], $userCoords[1], $latitude, $longitude);
            }
        }

        return new HubItemRow(
            entity: $entity,
            type: $this->entityType(),
            priority: $priority,
            timeToBegin: $timeToBegin,
            distance: $distance,
            beginAt: $beginAt,
            createdAt: $entity->getCreatedAt() ?? $now,
            finishAt: $this->extractFinishAt($entity),
            participantsCount: $this->extractParticipantsCount($entity),
        );
    }
}
