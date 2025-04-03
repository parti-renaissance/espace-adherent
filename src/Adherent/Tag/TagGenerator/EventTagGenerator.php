<?php

namespace App\Adherent\Tag\TagGenerator;

use App\Adherent\Tag\StaticTag\TagBuilder;
use App\Entity\Adherent;
use App\Entity\NationalEvent\EventInscription;
use App\Repository\NationalEvent\EventInscriptionRepository;

class EventTagGenerator extends AbstractTagGenerator
{
    public function __construct(
        private readonly EventInscriptionRepository $eventInscriptionRepository,
        private readonly TagBuilder $eventTagBuilder,
    ) {
    }

    public function generate(Adherent $adherent, array $previousTags): array
    {
        return array_map(
            fn (EventInscription $inscription) => $this->eventTagBuilder->buildForEvent($inscription->event),
            $this->eventInscriptionRepository->findAllForTags($adherent)
        );
    }
}
