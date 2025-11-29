<?php

declare(strict_types=1);

namespace App\JMEFilter\FilterBuilder;

use App\Adherent\Tag\TagEnum;
use App\JMEFilter\FilterGroup\ElectedRepresentativeFilterGroup;

class ElectTagsFilterBuilder extends AbstractTagsFilterBuilder
{
    protected function init(): void
    {
        $this->tags = TagEnum::getElectTags();
        $this->fieldName = 'elect_tags';
        $this->fieldLabel = 'Labels élu';
    }

    public function getGroup(string $scope, ?string $feature = null): string
    {
        return ElectedRepresentativeFilterGroup::class;
    }
}
