<?php

declare(strict_types=1);

namespace App\JMEFilter\FilterBuilder;

use App\JMEFilter\FilterCollectionBuilder;
use App\Scope\FeatureEnum;

class ContributionDatesFilterBuilder implements FilterBuilderInterface
{
    public function supports(string $scope, ?string $feature = null): bool
    {
        return true;
    }

    public function build(string $scope, ?string $feature = null, bool $isVox = false): array
    {
        $builder = new FilterCollectionBuilder()
            ->createDateInterval('first_membership', 'Première cotisation')
            ->setPosition(100)
            ->createDateInterval('last_membership', 'Dernière cotisation')
            ->setPosition(101)
        ;

        if ($isVox && FeatureEnum::CONTACTS === $feature) {
            $builder
                ->createDateInterval('registered', 'Création du compte')
                ->setPosition(102)
            ;
        }

        return $builder->getFilters();
    }
}
