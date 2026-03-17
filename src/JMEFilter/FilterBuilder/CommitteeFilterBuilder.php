<?php

declare(strict_types=1);

namespace App\JMEFilter\FilterBuilder;

use App\Entity\Committee;
use App\JMEFilter\FilterCollectionBuilder;
use App\Repository\CommitteeRepository;
use App\Scope\FeatureEnum;
use App\Scope\Scope;
use App\Scope\ScopeGeneratorResolver;

class CommitteeFilterBuilder implements FilterBuilderInterface
{
    public function __construct(
        private readonly ScopeGeneratorResolver $scopeGeneratorResolver,
        private readonly CommitteeRepository $committeeRepository,
    ) {
    }

    public function build(string $scope, ?string $feature = null, bool $isVox = false): array
    {
        $scopeObject = $this->scopeGeneratorResolver->generate();

        return new FilterCollectionBuilder()
            ->createSelect(\in_array($feature, [FeatureEnum::MESSAGES, FeatureEnum::PUBLICATIONS]) ? 'committee' : 'committeeUuids', 'Comités')
            ->setChoices($this->getCommitteeChoices($scopeObject))
            ->setMultiple(!\in_array($feature, [FeatureEnum::MESSAGES, FeatureEnum::PUBLICATIONS]))
            ->getFilters()
        ;
    }

    private function getCommitteeChoices(Scope $scope): array
    {
        return array_reduce(
            $this->committeeRepository->findInZones($scope->getZones()),
            static function ($carry, Committee $item) {
                $carry[$item->getUuid()->toString()] = $item->getName();

                return $carry;
            },
            []
        );
    }
}
