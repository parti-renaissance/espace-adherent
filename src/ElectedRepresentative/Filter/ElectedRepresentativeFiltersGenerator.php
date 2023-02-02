<?php

namespace App\ElectedRepresentative\Filter;

use App\ElectedRepresentative\Filter\FilterBuilder\ElectedRepresentativeFilterBuilderInterface;
use App\Filter\FilterInterface;

class ElectedRepresentativeFiltersGenerator
{
    /** @var ElectedRepresentativeFilterBuilderInterface[]|iterable */
    private iterable $builders;

    public function __construct(iterable $builders)
    {
        $this->builders = $builders;
    }

    /**
     * @return FilterInterface[]
     */
    public function generate(string $scope): array
    {
        $filters = [];

        foreach ($this->builders as $builder) {
            if ($builder->supports($scope)) {
                array_push($filters, ...$builder->build($scope));
            }
        }

        return $filters;
    }
}
