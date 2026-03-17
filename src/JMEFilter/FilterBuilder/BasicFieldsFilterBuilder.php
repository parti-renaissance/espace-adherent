<?php

declare(strict_types=1);

namespace App\JMEFilter\FilterBuilder;

use App\JMEFilter\FilterCollectionBuilder;
use App\JMEFilter\Types\DefinedTypes\AgeRange;
use App\JMEFilter\Types\DefinedTypes\GenderSelect;
use App\JMEFilter\Types\DefinedTypes\NationalitySelect;
use App\Scope\FeatureEnum;

class BasicFieldsFilterBuilder implements FilterBuilderInterface
{
    public function build(string $scope, ?string $feature = null, bool $isVox = false): array
    {
        $builder = new FilterCollectionBuilder()
            ->createFrom(GenderSelect::class)
            ->createFrom(AgeRange::class)
        ;

        if ($isVox && FeatureEnum::CONTACTS === $feature) {
            $builder->createFrom(NationalitySelect::class);
        }

        return $builder->getFilters();
    }
}
