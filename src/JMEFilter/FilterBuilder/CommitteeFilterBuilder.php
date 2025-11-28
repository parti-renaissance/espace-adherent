<?php

declare(strict_types=1);

namespace App\JMEFilter\FilterBuilder;

use App\Entity\Committee;
use App\JMEFilter\FilterCollectionBuilder;
use App\JMEFilter\FilterGroup\MilitantFilterGroup;
use App\Repository\CommitteeRepository;
use App\Scope\FeatureEnum;
use App\Scope\Scope;
use App\Scope\ScopeEnum;
use App\Scope\ScopeGeneratorResolver;

class CommitteeFilterBuilder implements FilterBuilderInterface
{
    public function __construct(
        private readonly ScopeGeneratorResolver $scopeGeneratorResolver,
        private readonly CommitteeRepository $committeeRepository,
    ) {
    }

    public function supports(string $scope, ?string $feature = null): bool
    {
        return \in_array($scope, [ScopeEnum::PRESIDENT_DEPARTMENTAL_ASSEMBLY, ScopeEnum::NATIONAL], true);
    }

    public function build(string $scope, ?string $feature = null): array
    {
        $scopeObject = $this->scopeGeneratorResolver->generate();

        return (new FilterCollectionBuilder())
            ->createSelect(\in_array($feature, [FeatureEnum::MESSAGES, FeatureEnum::PUBLICATIONS]) ? 'committee' : 'committeeUuids', 'Comités')
            ->withEmptyChoice(FeatureEnum::PUBLICATIONS === $feature)
            ->setChoices($this->getCommitteeChoices($scopeObject))
            ->setMultiple(!\in_array($feature, [FeatureEnum::MESSAGES, FeatureEnum::PUBLICATIONS]))
            ->getFilters()
        ;
    }

    private function getCommitteeChoices(Scope $scope): array
    {
        return array_reduce(
            ScopeEnum::ANIMATOR === $scope->getCode() ?
                $this->committeeRepository->findByUuid($scope->getCommitteeUuids())
                : $this->committeeRepository->findInZones($scope->getZones()),
            function ($carry, Committee $item) {
                $carry[$item->getUuid()->toString()] = $item->getName();

                return $carry;
            },
            []
        );
    }

    public function getGroup(string $scope, ?string $feature = null): string
    {
        return MilitantFilterGroup::class;
    }
}
