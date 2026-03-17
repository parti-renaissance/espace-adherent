<?php

declare(strict_types=1);

namespace App\JMEFilter;

use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class SupportedFilterCodesProvider
{
    public function __construct(
        private readonly FiltersGenerator $filtersGenerator,
        private readonly CacheInterface $cache,
    ) {
    }

    public function getCodes(string $scope, ?string $feature, bool $isVox = false): array
    {
        $cacheKey = \sprintf('filter_codes.%s.%s.%d', $scope, $feature ?? 'null', (int) $isVox);

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($scope, $feature, $isVox): array {
            $item->expiresAfter(null);

            $codes = [];
            foreach ($this->filtersGenerator->generate($scope, $feature, $isVox) as $group) {
                foreach ($group->getFilters() as $filter) {
                    $codes[] = $filter->getCode();
                }
            }

            return $codes;
        });
    }
}
