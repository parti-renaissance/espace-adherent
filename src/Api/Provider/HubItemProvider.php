<?php

declare(strict_types=1);

namespace App\Api\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\PaginatorInterface;
use ApiPlatform\State\Pagination\TraversablePaginator;
use ApiPlatform\State\ProviderInterface;
use App\Api\DTO\HubItemView;
use App\Api\Provider\Hub\ActionHubFetcher;
use App\Api\Provider\Hub\EventHubFetcher;
use App\Api\Provider\Hub\HubItemRow;
use App\Api\Provider\Hub\HubItemSorter;

class HubItemProvider implements ProviderInterface
{
    public const int DEFAULT_PAGE_SIZE = 100;
    public const int MAX_PAGE_SIZE = 300;
    public const int MAX_HUB_PAGE_FETCH = 600;

    public function __construct(
        private readonly EventHubFetcher $eventFetcher,
        private readonly ActionHubFetcher $actionFetcher,
        private readonly HubItemSorter $sorter,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): PaginatorInterface
    {
        $filters = $context['filters'] ?? [];
        $page = max(1, (int) ($filters['page'] ?? 1));
        $pageSize = $this->resolvePageSize($filters);
        $hasUserCoords = $this->hasUserCoords($filters);
        [$orderKey, $orderDirection] = $this->resolveOrder($filters);

        $fetchLimit = min($page * $pageSize, self::MAX_HUB_PAGE_FETCH);

        $rows = [
            ...$this->eventFetcher->fetch($filters, $context, $operation, $fetchLimit),
            ...$this->actionFetcher->fetch($filters, $context, $operation, $fetchLimit),
        ];

        usort($rows, fn (HubItemRow $a, HubItemRow $b) => $this->sorter->compare($a, $b, $hasUserCoords, $orderKey, $orderDirection));

        $slice = \array_slice($rows, ($page - 1) * $pageSize, $pageSize);

        $views = array_map(
            static fn (HubItemRow $row) => new HubItemView($row->type, $row->entity),
            $slice
        );

        $totalItems = $this->eventFetcher->count($filters, $context, $operation)
            + $this->actionFetcher->count($filters, $context, $operation);

        return new TraversablePaginator(new \ArrayIterator($views), $page, $pageSize, $totalItems);
    }

    /**
     * @return array{0: ?string, 1: string}
     */
    private function resolveOrder(array $filters): array
    {
        $order = $filters['order'] ?? null;

        if (!\is_array($order) || [] === $order) {
            return [null, 'asc'];
        }

        $key = (string) array_key_first($order);

        if (!\in_array($key, HubItemSorter::SUPPORTED_ORDERS, true)) {
            return [null, 'asc'];
        }

        $direction = 'desc' === strtolower((string) $order[$key]) ? 'desc' : 'asc';

        return [$key, $direction];
    }

    private function resolvePageSize(array $filters): int
    {
        $requested = (int) ($filters['page_size'] ?? self::DEFAULT_PAGE_SIZE);

        if ($requested <= 0) {
            return self::DEFAULT_PAGE_SIZE;
        }

        return min($requested, self::MAX_PAGE_SIZE);
    }

    private function hasUserCoords(array $filters): bool
    {
        return isset($filters['lat'], $filters['lng'])
            && is_numeric($filters['lat'])
            && is_numeric($filters['lng']);
    }
}
