<?php

namespace App\Adherent\Tag\TagGenerator;

use App\Adherent\Tag\TagEnum;
use App\Entity\Adherent;
use App\Repository\NationalEvent\EventInscriptionRepository;

class EventTagGenerator extends AbstractTagGenerator
{
    public function __construct(private readonly EventInscriptionRepository $eventInscriptionRepository)
    {
    }

    public function generate(Adherent $adherent, array $previousTags): array
    {
        if ($this->eventInscriptionRepository->findAllByAdherent($adherent)) {
            return [TagEnum::MEETING_LILLE_09_03];
        }

        return [];
    }
}
