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
        $inscriptions = $this->eventInscriptionRepository->findAllForTags($adherent);

        return array_values(array_unique(array_merge(
            array_map(
                fn (EventInscription $inscription) => $this->eventTagBuilder->buildForEvent($inscription->event),
                array_filter(
                    $inscriptions,
                    static fn (EventInscription $inscription) => $inscription->event->endDate > new \DateTime()
                )
            ),
            array_map(
                fn (EventInscription $inscription) => $this->eventTagBuilder->buildForEvent($inscription->event, true),
                array_filter(
                    $inscriptions,
                    static fn (EventInscription $inscription) => $inscription->firstTicketScannedAt && $inscription->event->startDate < new \DateTime()
                )
            ),
        )));
    }
}
