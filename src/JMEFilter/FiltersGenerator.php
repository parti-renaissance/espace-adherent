<?php

declare(strict_types=1);

namespace App\JMEFilter;

use App\JMEFilter\FilterBuilder\FilterBuilderInterface;
use App\JMEFilter\FilterGroup\AbstractFilterGroup;
use App\JMEFilter\FilterGroup\FilterGroupInterface;
use App\JMEFilter\Layout\FilterLayoutResolver;
use Psr\Container\ContainerInterface;

class FiltersGenerator
{
    public function __construct(
        private readonly FilterLayoutResolver $layoutResolver,
        private readonly ContainerInterface $builderLocator,
    ) {
    }

    /**
     * @return FilterGroupInterface[]
     */
    public function generate(string $scope, ?string $feature = null, bool $isVox = false): array
    {
        $layout = $this->layoutResolver->resolve($scope, $feature, $isVox);
        /** @var FilterGroupInterface[] $groups */
        $groups = [];

        foreach ($layout->getGroupConfigs() as $groupConfig) {
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

                if (!$builder->supports($scope, $feature, $isVox)) {
                    continue;
                }

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
