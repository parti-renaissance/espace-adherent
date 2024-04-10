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
            TagEnum::PROCURATION_REQUEST,
            TagEnum::PROCURATION_REQUEST.'--',
            TagEnum::PROCURATION_PROXY,
            TagEnum::PROCURATION_PROXY.'--',
        ];
        $this->fieldName = 'static_tags';
        $this->fieldLabel = 'Labels divers';
        $this->fullTag = false;
    }

    public function getGroup(): string
    {
        return MilitantFilterGroup::class;
    }
}
