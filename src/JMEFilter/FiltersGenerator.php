<?php

declare(strict_types=1);

namespace App\JMEFilter;

use App\JMEFilter\FilterBuilder\FilterBuilderInterface;
use App\JMEFilter\FilterGroup\AbstractFilterGroup;
use App\JMEFilter\FilterGroup\FilterGroupInterface;
use App\JMEFilter\Layout\FilterLayoutResolver;
use Psr\Container\ContainerInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class FiltersGenerator
{
    public const CACHE_TAG = 'filters';
    private const CACHE_TTL = 3600;

    public function __construct(
        private readonly FilterLayoutResolver $layoutResolver,
        private readonly ContainerInterface $builderLocator,
        private readonly TagAwareCacheInterface $cache,
    ) {
    }

    /**
     * @return FilterGroupInterface[]
     */
    public function generate(string $scope, ?string $feature = null, bool $isVox = false): array
    {
        $cacheKey = \sprintf('filters.%s.%s.%d', $scope, $feature ?? 'null', (int) $isVox);

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($scope, $feature, $isVox): array {
            $item->expiresAfter(self::CACHE_TTL);
            $item->tag(self::CACHE_TAG);

            return $this->doGenerate($scope, $feature, $isVox);
        });
    }

    /**
     * @return FilterGroupInterface[]
     */
    private function doGenerate(string $scope, ?string $feature, bool $isVox): array
    {
        $layout = $this->layoutResolver->resolve($scope, $feature, $isVox);
        /** @var FilterGroupInterface[] $groups */
        $groups = [];

        foreach ($layout->getGroupConfigs($scope) as $groupConfig) {
            /** @var AbstractFilterGroup $group */
            $group = new ($groupConfig->groupClass)($scope, $feature, $isVox);

            if (null !== $groupConfig->labelOverride) {
                $group->label = $groupConfig->labelOverride;
            }

            foreach ($groupConfig->filters as $filterConfig) {
                if (!$this->builderLocator->has($filterConfig->builderClass)) {
                    continue;
                }

                /** @var FilterBuilderInterface $builder */
                $builder = $this->builderLocator->get($filterConfig->builderClass);

                foreach ($builder->build($scope, $feature, $isVox) as $filter) {
                    $group->addFilter($filter);
                }
            }

            if (\count($group->getFilters()) > 0) {
                $groups[] = $group;
            }
        }

        return $groups;
    }
}
