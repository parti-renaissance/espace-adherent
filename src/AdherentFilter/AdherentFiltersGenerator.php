<?php

namespace App\AdherentFilter;

use App\AdherentFilter\FilterBuilder\AdherentFilterBuilderInterface;
use App\Filter\FilterInterface;

class AdherentFiltersGenerator
{
    /** @var AdherentFilterBuilderInterface[]|iterable */
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

        return $filters;
    }
}
