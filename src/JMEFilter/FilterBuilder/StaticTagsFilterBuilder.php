<?php

namespace App\JMEFilter\FilterBuilder;

use App\Adherent\Tag\TagEnum;
use App\JMEFilter\FilterGroup\MilitantFilterGroup;

class StaticTagsFilterBuilder extends AbstractTagsFilterBuilder
{
    protected function init(): void
    {
        $this->tags = [
            TagEnum::MEETING_LILLE_09_03,
            TagEnum::MEETING_LILLE_09_03.'--',
        ];
        $this->fieldName = 'static_tags';
        $this->fieldLabel = 'Labels statiques';
        $this->fullTag = false;
    }

    public function getGroup(): string
    {
        return MilitantFilterGroup::class;
    }
}
