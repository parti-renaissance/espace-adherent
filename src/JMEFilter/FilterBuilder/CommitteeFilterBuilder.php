<?php

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
        return \in_array($scope, ScopeEnum::ALL, true)
            && ScopeEnum::ANIMATOR !== $scope;
    }

    public function build(string $scope, ?string $feature = null): array
    {
        $scope = $this->scopeGeneratorResolver->generate();

        return (new FilterCollectionBuilder())
            ->createSelect(\in_array($feature, [FeatureEnum::MESSAGES, FeatureEnum::MESSAGES_VOX]) ? 'committee' : 'committeeUuids', 'Comités')
            ->setChoices($this->getCommitteeChoices($scope))
            ->setMultiple(!\in_array($feature, [FeatureEnum::MESSAGES, FeatureEnum::MESSAGES_VOX]))
            ->setRequired(\in_array($feature, [FeatureEnum::MESSAGES, FeatureEnum::MESSAGES_VOX]) && ScopeEnum::ANIMATOR === $scope->getCode())
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

    public function getGroup(): string
    {
        return MilitantFilterGroup::class;
    }
}
