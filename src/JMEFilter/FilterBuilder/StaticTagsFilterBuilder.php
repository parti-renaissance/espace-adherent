<?php

namespace App\JMEFilter\FilterBuilder;

use App\Adherent\Tag\StaticTag\EventTagBuilder;
use App\Adherent\Tag\TagEnum;
use App\Adherent\Tag\TagTranslator;
use App\JMEFilter\FilterGroup\MilitantFilterGroup;

class StaticTagsFilterBuilder extends AbstractTagsFilterBuilder
{
    public function __construct(TagTranslator $translator, private readonly EventTagBuilder $eventTagBuilder)
    {
        parent::__construct($translator);
    }

    protected function init(): void
    {
        $this->tags = array_unique(array_merge(
            ...array_map(
                fn (string $tag) => [$tag, $tag.'--'],
                array_merge($this->eventTagBuilder->buildAll(), TagEnum::getStaticTags())
            )
        ));

        $this->fieldName = 'static_tags';
        $this->fieldLabel = 'Labels divers';
        $this->fullTag = false;
    }

    public function getGroup(): string
    {
        return MilitantFilterGroup::class;
    }
}
