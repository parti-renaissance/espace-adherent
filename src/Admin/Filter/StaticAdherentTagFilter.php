<?php

namespace App\Admin\Filter;

use App\Adherent\Tag\StaticTag\EventTagBuilder;
use App\Adherent\Tag\TagEnum;
use Symfony\Contracts\Service\Attribute\Required;

class StaticAdherentTagFilter extends AdherentTagFilter
{
    private EventTagBuilder $eventTagBuilder;

    #[Required]
    public function setEventTagBuilder(EventTagBuilder $eventTagBuilder): void
    {
        $this->eventTagBuilder = $eventTagBuilder;
    }

    protected function getTags(): array
    {
        return array_merge($this->eventTagBuilder->buildAll(), TagEnum::getStaticTags());
    }
}
