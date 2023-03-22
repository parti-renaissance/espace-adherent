<?php

namespace App\JMEFilter\FilterBuilder;

use App\JMEFilter\FilterCollectionBuilder;
use App\Repository\CommitteeRepository;
use App\Scope\ScopeEnum;
use App\Scope\ScopeGeneratorResolver;

class CommitteeFilterBuilder implements FilterBuilderInterface
{
    public function __construct(
        private readonly ScopeGeneratorResolver $scopeGeneratorResolver,
        private readonly CommitteeRepository $committeeRepository
    ) {
    }

    public function supports(string $scope, string $feature = null): bool
    {
        return \in_array($scope, ScopeEnum::ALL, true);
    }

    public function build(string $scope, string $feature = null): array
    {
        $scope = $this->scopeGeneratorResolver->generate();

        return (new FilterCollectionBuilder())
            ->createSelect('committeeUuids', 'Comités')
            ->setChoices($this->committeeRepository->findCommitteeForFilterBuilder($scope->getZones()))
            ->setMultiple(true)
            ->getFilters()
        ;
    }
}
