<?php

declare(strict_types=1);

namespace App\JMEFilter;

use App\JMEFilter\FilterBuilder\FilterBuilderInterface;
use App\JMEFilter\FilterGroup\FilterGroupInterface;

class FiltersGenerator
{
    /** @var FilterBuilderInterface[]|iterable */
    private iterable $builders;

    public function __construct(iterable $builders)
    {
        $this->builders = $builders;
    }

    /**
     * @return FilterInterface[]
     */
    public function generate(string $scope, ?string $feature = null): array
    {
        $filters = [];

        foreach ($this->builders as $builder) {
            if ($builder->supports($scope, $feature)) {
                $groupClass = $builder->getGroup($scope, $feature);

                if (!\array_key_exists($groupClass, $filters)) {
                    $filters[$groupClass] = new $groupClass($scope, $feature);
                }

                /** @var FilterGroupInterface $filterGroup */
                $filterGroup = $filters[$groupClass];

                foreach ($builder->build($scope, $feature) as $filter) {
                    $filterGroup->addFilter($filter);
                }
            }
        }

        usort($filters, fn (FilterGroupInterface $a, FilterGroupInterface $b) => $a->getPosition() <=> $b->getPosition());

        return array_values($filters);
    }
}
