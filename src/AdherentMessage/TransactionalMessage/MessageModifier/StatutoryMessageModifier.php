<?php

namespace App\AdherentMessage\TransactionalMessage\MessageModifier;

use App\Entity\AdherentMessage\AdherentMessageInterface;
use App\Entity\AdherentMessage\Filter\MessageFilter;
use App\Entity\AdherentMessage\StatutoryAdherentMessage;
use App\Repository\AdherentRepository;

class StatutoryMessageModifier implements MessageModifierInterface
{
    public function __construct(private readonly AdherentRepository $adherentRepository)
    {
    }

    public function support(AdherentMessageInterface $message): bool
    {
        return $message instanceof StatutoryAdherentMessage;
    }

    public function modify(AdherentMessageInterface $message): void
    {
        /** @var MessageFilter $filter */
        $filter = $message->getFilter();

        $message->setRecipientCount($this->adherentRepository->countInZones(
            $filter->getZones()->toArray(),
            true,
            false
        ));
    }
}
