<?php

declare(strict_types=1);

namespace App\Api\Provider\Hub;

use ApiPlatform\Metadata\Operation;
use App\Entity\Action\Action;
use App\Entity\Event\Event;
use Doctrine\ORM\QueryBuilder;

abstract class AbstractHubFetcher
{
    abstract protected function buildQuery(array $filters, array $apiContext, ?Operation $operation): QueryBuilder;

    abstract protected function entityType(): string;

    abstract protected function extractBeginAt(Event|Action $entity): ?\DateTimeInterface;

    /**
     * @return HubItemRow[]
     */
    public function fetch(array $filters, array $apiContext, ?Operation $operation, int $limit): array
    {
        $queryBuilder = $this->buildQuery($filters, $apiContext, $operation);
        $queryBuilder->setMaxResults($limit);

        /** @var array<Event|Action> $entities */
        $entities = $queryBuilder->getQuery()->getResult();

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
            ->resetDQLPart('orderBy')
        ;

        $dql = $queryBuilder->getDQL();
        $queryBuilder->setParameters($queryBuilder->getParameters()->filter(
            static fn ($parameter) => str_contains($dql, ':'.$parameter->getName())
        ));

        return (int) $queryBuilder->getQuery()->getSingleScalarResult();
    }

    protected function applyHubDateFilter(QueryBuilder $queryBuilder, array $filters, string $dateField): void
    {
        $beginAt = $filters['beginAt'] ?? null;

        if (!\is_array($beginAt)) {
            return;
        }

        if (isset($beginAt['strictly_after'])) {
            $queryBuilder
                ->andWhere(\sprintf('%s > :hub_date_strictly_after', $dateField))
                ->setParameter('hub_date_strictly_after', new \DateTimeImmutable($beginAt['strictly_after']))
            ;
        }

        if (isset($beginAt['after'])) {
            $queryBuilder
                ->andWhere(\sprintf('%s >= :hub_date_after', $dateField))
                ->setParameter('hub_date_after', new \DateTimeImmutable($beginAt['after']))
            ;
        }

        if (isset($beginAt['strictly_before'])) {
            $queryBuilder
                ->andWhere(\sprintf('%s < :hub_date_strictly_before', $dateField))
                ->setParameter('hub_date_strictly_before', new \DateTimeImmutable($beginAt['strictly_before']))
            ;
        }

        if (isset($beginAt['before'])) {
            $queryBuilder
                ->andWhere(\sprintf('%s <= :hub_date_before', $dateField))
                ->setParameter('hub_date_before', new \DateTimeImmutable($beginAt['before']))
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
        );
    }
}
