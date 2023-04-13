<?php

namespace App\JMEFilter\FilterBuilder;

use App\Entity\Committee;
use App\JMEFilter\FilterCollectionBuilder;
use App\Repository\CommitteeRepository;
use App\Scope\FeatureEnum;
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
        return \in_array($scope, ScopeEnum::ALL, true)
            && ScopeEnum::ANIMATOR !== $scope
        ;
    }

    public function build(string $scope, string $feature = null): array
    {
        $scope = $this->scopeGeneratorResolver->generate();

        return (new FilterCollectionBuilder())
            ->createSelect(FeatureEnum::MESSAGES === $feature ? 'committee' : 'committeeUuids', 'Comités')
            ->setChoices(
                array_reduce($this->committeeRepository->findInZones($scope->getZones()), function ($carry, Committee $item) {
                    $carry[$item->getUuid()->toString()] = $item->getName();

                    return $carry;
                }, [])
            )
            ->setMultiple(FeatureEnum::MESSAGES !== $feature)
            ->getFilters()
        ;
    }
}
