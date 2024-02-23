<?php

namespace App\JMEFilter\FilterBuilder;

use App\Adherent\Tag\TagEnum;
use App\JMEFilter\FilterGroup\MilitantFilterGroup;

class AdherentTagsFilterBuilder extends AbstractTagsFilterBuilder
{
    protected function init(): void
    {
        $this->tags = TagEnum::getAdherentTags();
        $this->fieldName = 'adherent_tags';
        $this->fieldLabel = 'Labels adhérent';
    }

    public function getGroup(): string
    {
        return MilitantFilterGroup::class;
    }
}
