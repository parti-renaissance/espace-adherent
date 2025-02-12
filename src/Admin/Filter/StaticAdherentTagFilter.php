<?php

namespace App\Admin\Filter;

use App\Adherent\Tag\StaticTag\TagBuilder;
use Symfony\Contracts\Service\Attribute\Required;

class StaticAdherentTagFilter extends AdherentTagFilter
{
    private TagBuilder $tagBuilder;

    #[Required]
    public function setTagBuilder(TagBuilder $eventTagBuilder): void
    {
        $this->tagBuilder = $eventTagBuilder;
    }

    protected function getTags(): array
    {
        return $this->tagBuilder->buildAll();
    }
}
