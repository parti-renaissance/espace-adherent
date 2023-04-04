<?php

namespace App\JMEFilter;

use App\JMEFilter\FilterBuilder\FilterBuilderInterface;

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
    public function generate(string $scope, string $feature = null): array
    {
        $filters = [];

        foreach ($this->builders as $builder) {
            if ($builder->supports($scope, $feature)) {
                array_push($filters, ...$builder->build($scope, $feature));
            }
        }

        usort($filters, function (FilterInterface $filter1, FilterInterface $filter2) {
            return $filter1->getPosition() <=> $filter2->getPosition();
        });

        return $filters;
    }
}
